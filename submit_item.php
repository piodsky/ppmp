<?php
// Submit Item page - PHP-based authentication like dashboard
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../apiPPMP');
$dotenv->load();

$host     = $_ENV['DB_HOST'];
$dbname   = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once __DIR__ . "/../apiPPMP/token_helper.php";
TokenHelper::init($conn);

// Check for token in cookie or Authorization header
$token = null;

// First check for token in cookie (secure method)
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}
// Fallback to Authorization header
elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
        $token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
    }
}

if (!$token) {
    header("Location: login.php");
    exit();
}

// Validate token
$validation = TokenHelper::validateToken($token);
if (!$validation['valid']) {
    header("Location: login.php");
    exit();
}

// Set user data from token validation
$user_id = $validation['user_id'];
$username = $validation['username'];
$firstname = $validation['firstname'];
$lastname = $validation['lastname'];
$role = $validation['role'];
$department = $validation['department'];
$profile_picture = $validation['profile_picture'] ?? '';

// Set user data for JavaScript
$user = $username;
$user_role = $role;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Item | PPMP System</title>

    <!-- Argon Core CSS -->
    <link rel="stylesheet" href="argondashboard/assets/css/argon-dashboard.css">
    <!-- Local Fonts -->
    <link rel="stylesheet" href="argondashboard/assets/css/custom-fonts.css">
    <!-- Nucleo Icons -->
    <link rel="stylesheet" href="argondashboard/assets/css/nucleo-icons.css">
    <link rel="stylesheet" href="argondashboard/assets/css/nucleo-svg.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/font-awesome.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/logo.svg">

    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-accent: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --text-primary: #1e3a8a;
            --text-muted: #6b7280;
            --border-light: rgba(0,0,0,0.1);
            --shadow-color: rgba(0,0,0,0.1);
        }

        [data-theme="dark"] {
            --bg-primary: #374151;
            --bg-secondary: #1f2937;
            --bg-accent: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --text-primary: #ffffff;
            --text-muted: #9ca3af;
            --border-light: rgba(255,255,255,0.1);
            --shadow-color: rgba(0,0,0,0.4);
        }

        .section-title {
            background: var(--bg-accent);
            color: #ffffff !important;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px var(--shadow-color);
            border: 1px solid var(--border-light);
        }

        .form-section {
            border: none;
            padding: 25px;
            margin-bottom: 25px;
            background: var(--bg-secondary);
            border-radius: 15px;
            box-shadow: 0 4px 20px var(--shadow-color);
            border: 1px solid var(--border-light);
        }

        .form-control {
            border-radius: 6px;
            border: 1px solid var(--border-light);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #1e40af;
            box-shadow: 0 0 0 0.15rem rgba(30, 64, 175, 0.25);
            background: var(--bg-primary);
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4);
        }

        body {
            color: var(--text-primary) !important;
        }

        h1, h2, h3, h4, h5, h6, p, span, div, label {
            color: var(--text-primary);
        }
    </style>
</head>

<body class="g-sidenav-show" data-theme="light" style="background: var(--bg-primary); min-height: 100vh;">

<?php include 'sidebar.php'; ?>

<main class="main-content position-relative border-radius-lg ps ps--active-y" style="margin-left: 280px; padding: 1.5rem;">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <img src="assets/logo.svg" alt="PPMP Logo" style="width: 50px; height: 50px; margin-right: 15px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                <div>
                    <h3 class="text-primary mb-0">Submit New Item</h3>
                    <small class="text-muted">Request approval for a new item to be added to the system</small>
                </div>
            </div>
            <button class="btn btn-outline-secondary" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
        </div>

        <div class="form-section">
            <div class="section-title">
                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Item Details</h5>
            </div>

            <form id="submitItemForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="itemCode" class="form-label">Item Code *</label>
                        <input type="text" class="form-control" id="itemCode" required>
                        <small class="text-muted">Unique identifier for the item</small>
                    </div>
                    <div class="col-md-6">
                        <label for="itemName" class="form-label">Item Name *</label>
                        <input type="text" class="form-control" id="itemName" required>
                        <small class="text-muted">Display name of the item</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="unit" class="form-label">Unit *</label>
                        <input type="text" class="form-control" id="unit" required>
                        <small class="text-muted">Unit of measurement (e.g., pcs, kg, box)</small>
                    </div>
                    <div class="col-md-6">
                        <label for="unitCost" class="form-label">Unit Cost *</label>
                        <input type="number" class="form-control" id="unitCost" step="0.01" min="0" required>
                        <small class="text-muted">Cost per unit in Philippine Peso</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category">
                        <small class="text-muted">Optional category for organization</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description *</label>
                    <textarea class="form-control" id="description" rows="3" required></textarea>
                    <small class="text-muted">Detailed description of the item</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearForm()">
                        <i class="fas fa-eraser"></i> Clear Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Success/Error Messages -->
        <div id="messageContainer" style="display: none;"></div>
    </div>
</main>

<!-- Argon Core JS -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

<script src="../api_config.js.php"></script>

<script>
// Theme Management
class SubmitItemThemeManager {
    constructor() {
        this.currentTheme = this.getInitialTheme();
        this.init();
    }

    getInitialTheme() {
        const savedTheme = localStorage.getItem('ppmp-theme');
        if (savedTheme) {
            return savedTheme;
        }
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.setupToggle();
        this.updateToggleIcon();
    }

    setupToggle() {
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.toggleTheme();
            });
        }
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.applyTheme(this.currentTheme);
        this.saveTheme();
        this.updateToggleIcon();
    }

    applyTheme(theme) {
        document.body.setAttribute('data-theme', theme);
        if (theme === 'dark') {
            document.body.style.background = 'linear-gradient(135deg, #374151 0%, #1f2937 100%)';
            document.body.style.color = '#ffffff';
        } else {
            document.body.style.background = '#ffffff';
            document.body.style.color = '#1e3a8a';
        }
    }

    updateToggleIcon() {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            if (this.currentTheme === 'dark') {
                icon.className = 'fas fa-sun';
                document.getElementById('themeToggle').title = 'Switch to Light Theme';
            } else {
                icon.className = 'fas fa-moon';
                document.getElementById('themeToggle').title = 'Switch to Dark Theme';
            }
        }
    }

    saveTheme() {
        localStorage.setItem('ppmp-theme', this.currentTheme);
    }
}

// Form handling
document.addEventListener('DOMContentLoaded', function() {
    new SubmitItemThemeManager();

    const form = document.getElementById('submitItemForm');
    form.addEventListener('submit', handleSubmit);
});

function handleSubmit(event) {
    event.preventDefault();

    const formData = {
        item_code: document.getElementById('itemCode').value.trim(),
        item_name: document.getElementById('itemName').value.trim(),
        description: document.getElementById('description').value.trim(),
        unit: document.getElementById('unit').value.trim(),
        unit_cost: parseFloat(document.getElementById('unitCost').value),
        category: document.getElementById('category').value.trim(),
        submitted_by: '<?php echo $user; ?>'
    };

    // Validate required fields
    if (!formData.item_code || !formData.item_name || !formData.description ||
        !formData.unit || isNaN(formData.unit_cost) || formData.unit_cost <= 0) {
        showMessage('Please fill in all required fields with valid values.', 'danger');
        return;
    }

    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;

    fetch(`${API_BASE_URL}/api_submit_item.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            clearForm();
        } else {
            showMessage(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Submit error:', error);
        showMessage('Network error. Please try again.', 'danger');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function clearForm() {
    document.getElementById('submitItemForm').reset();
    document.getElementById('messageContainer').style.display = 'none';
}

function showMessage(message, type) {
    const container = document.getElementById('messageContainer');
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    container.style.display = 'block';
}
</script>

</body>
</html>
