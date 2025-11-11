<?php
// Manage Pending Items page - API-based authentication

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

// Validate token via API call
$apiUrl = 'https://sakatamalaybalay.com/api/ppmp/api_verify_token.php';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ],
        'timeout' => 10
    ]
]);

$response = file_get_contents($apiUrl, false, $context);
if ($response === false) {
    header("Location: login.php");
    exit();
}

$data = json_decode($response, true);
if (!$data || $data['status'] !== 'success') {
    header("Location: login.php");
    exit();
}

// Set user data from API response
$user_id = $data['user']['id'];
$username = $data['user']['username'];
$firstname = $data['user']['firstname'];
$lastname = $data['user']['lastname'];
$role = $data['user']['role'];
$department = $data['user']['department'];
$profile_picture = $data['user']['profile_picture'] ?? '';

// Check if user is admin
if ($role !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Set user data for JavaScript
$user = $username;
$user_role = $role;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pending Items | PPMP System</title>

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

        .table {
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 8px 4px;
            color: var(--text-primary);
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.2;
        }

        .table thead th {
            background: var(--bg-accent);
            color: #ffffff !important;
            border: none;
            font-weight: 600;
            border: 1px solid var(--border-light);
        }

        .table tbody tr {
            background: rgba(255,255,255,0.05);
            border-bottom: 1px solid var(--border-light);
        }

        .table tbody tr:hover {
            background: rgba(255,255,255,0.1);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-approved {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-rejected {
            background: #ffebee;
            color: #c62828;
        }

        .btn-success {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(56, 161, 105, 0.3);
            color: white;
            font-size: 0.85rem;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            color: white;
            font-size: 0.8rem;
            padding: 4px 8px;
        }

        .btn-info {
            background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            font-size: 0.8rem;
        }

        body {
            color: var(--text-primary) !important;
        }

        h1, h2, h3, h4, h5, h6, p, span, div, label {
            color: var(--text-primary);
        }

        .modal-content {
            background: var(--bg-primary) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-light) !important;
        }

        .modal-header {
            background: var(--bg-secondary) !important;
            border-bottom: 1px solid var(--border-light) !important;
        }

        .modal-body {
            background: var(--bg-primary) !important;
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
                    <h3 class="text-primary mb-0">Manage Pending Items</h3>
                    <small class="text-muted">Review and approve items submitted by users</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <button class="btn btn-info" onclick="loadPendingItems()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Pending Item Approvals</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="pendingItemsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Unit Cost</th>
                                <th>Category</th>
                                <th>Submitted By</th>
                                <th>Submitted Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingItemsTableBody">
                            <!-- Items will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div id="messageContainer" style="display: none;"></div>
    </div>
</main>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Review Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="itemDetails">
                    <!-- Item details will be loaded here -->
                </div>
                <div class="form-group">
                    <label for="adminComments">Comments (Optional)</label>
                    <textarea class="form-control" id="adminComments" rows="3" placeholder="Add any comments for the user..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="reviewItem('reject')">
                    <i class="fas fa-times"></i> Reject
                </button>
                <button type="button" class="btn btn-success" onclick="reviewItem('approve')">
                    <i class="fas fa-check"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Argon Core JS -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

<script src="../api_config.js.php"></script>

<script>
// Theme Management
class ManageItemsThemeManager {
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
        loadPendingItems(); // Load items on page load
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

let currentItemId = null;

document.addEventListener('DOMContentLoaded', function() {
    new ManageItemsThemeManager();
});

function loadPendingItems() {
    const tableBody = document.getElementById('pendingItemsTableBody');

    // Show loading
    tableBody.innerHTML = `
        <tr>
            <td colspan="11" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading pending items...</p>
            </td>
        </tr>
    `;

    fetch(`${API_BASE_URL}/api_get_pending_items.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPendingItems(data.pending_items);
            } else {
                showMessage('Error loading pending items: ' + data.message, 'danger');
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h5 class="text-danger">Error</h5>
                            <p class="text-muted">${data.message}</p>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading pending items:', error);
            showMessage('Network error. Please try again.', 'danger');
        });
}

function displayPendingItems(items) {
    const tableBody = document.getElementById('pendingItemsTableBody');

    if (items.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No pending items</h5>
                    <p class="text-muted">All items have been reviewed.</p>
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = '';

    items.forEach((item, index) => {
        const statusClass = `status-${item.status}`;
        const formattedDate = new Date(item.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const actions = item.status === 'pending' ?
            `<button class="btn btn-info btn-sm" onclick="openReviewModal(${item.id})">
                <i class="fas fa-eye"></i> Review
             </button>` :
            `<span class="text-muted small">Reviewed by ${item.reviewed_by}</span>`;

        tableBody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.item_code}</strong></td>
                <td>${item.item_name}</td>
                <td>${item.description}</td>
                <td>${item.unit}</td>
                <td>₱${item.unit_cost.toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                <td>${item.category || 'N/A'}</td>
                <td>${item.submitted_by}</td>
                <td><small>${formattedDate}</small></td>
                <td><span class="status-badge ${statusClass}">${item.status.toUpperCase()}</span></td>
                <td>${actions}</td>
            </tr>
        `;
    });
}

function openReviewModal(itemId) {
    currentItemId = itemId;

    // Get item details
    fetch(`${API_BASE_URL}/api_load_ppmp.php?id=0&item_id=${itemId}`)
        .then(response => response.json())
        .then(data => {
            // For now, we'll get the item from the table row
            const row = document.querySelector(`button[onclick="openReviewModal(${itemId})"]`).closest('tr');
            const cells = row.querySelectorAll('td');

            const itemDetails = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Item Code:</strong> ${cells[1].textContent}</p>
                        <p><strong>Item Name:</strong> ${cells[2].textContent}</p>
                        <p><strong>Unit:</strong> ${cells[4].textContent}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Unit Cost:</strong> ${cells[5].textContent}</p>
                        <p><strong>Category:</strong> ${cells[6].textContent}</p>
                        <p><strong>Submitted By:</strong> ${cells[7].textContent}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <p><strong>Description:</strong></p>
                        <p class="text-muted">${cells[3].textContent}</p>
                    </div>
                </div>
            `;

            document.getElementById('itemDetails').innerHTML = itemDetails;
            document.getElementById('adminComments').value = '';

            $('#reviewModal').modal('show');
        })
        .catch(error => {
            console.error('Error loading item details:', error);
            showMessage('Error loading item details.', 'danger');
        });
}

function reviewItem(action) {
    if (!currentItemId) return;

    const comments = document.getElementById('adminComments').value.trim();

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    fetch(`${API_BASE_URL}/api_approve_item.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            item_id: currentItemId,
            action: action,
            admin_comments: comments,
            reviewed_by: '<?php echo $user; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            $('#reviewModal').modal('hide');
            loadPendingItems(); // Refresh the list
        } else {
            showMessage(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Review error:', error);
        showMessage('Network error. Please try again.', 'danger');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
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

    // Auto-hide after 5 seconds
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}
</script>

</body>
</html>
