<?php
// View Database Items page - PHP-based authentication like dashboard
set_time_limit(120); // Increase execution time limit
ini_set('memory_limit', '512M'); // Increase memory limit

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload dependencies
use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../apiPPMP');
$dotenv->load();

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

// Validate token via API call (like dashboard.php)
$apiUrl = $_ENV['API_BASE_URL'] . '/api_verify_token.php';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ],
        'timeout' => 60
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

// Set user data for JavaScript
$user = $username;
$user_role = $role;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Database Items | PPMP System</title>

    <!-- Argon Core CSS -->
    <link rel="stylesheet" href="argondashboard/assets/css/argon-dashboard.css">
    <!-- Local Fonts -->
    <link rel="stylesheet" href="css/custom-fonts.css">
    <!-- Nucleo Icons -->
    <link rel="stylesheet" href="argondashboard/assets/css/nucleo-icons.css">
    <link rel="stylesheet" href="argondashboard/assets/css/nucleo-svg.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/font-awesome.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/logo.svg">

    <script>
    // Define API_BASE_URL for this page
    const API_BASE_URL = "<?php echo $_ENV['API_BASE_URL'] ?? '/SystemsMISPYO/PPMP/apiPPMP'; ?>";

    // Load items via AJAX without authentication
    let preloadedItems = [];

    function loadItemsDirectly() {
        return fetch(API_BASE_URL + '/get_items_direct.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                preloadedItems = data.items;
                console.log('Loaded', preloadedItems.length, 'items directly');
                return preloadedItems;
            } else {
                console.error('Failed to load items:', data.error);
                return [];
            }
        })
        .catch(error => {
            console.error('Error loading items directly:', error);
            return [];
        });
    }

    // Search items function
    function searchItems(searchParams) {
        const queryString = new URLSearchParams(searchParams).toString();
        return fetch(`${API_BASE_URL}/search_items.php?${queryString}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                searchResults = data.items;
                console.log('Search found', searchResults.length, 'items');
                return searchResults;
            } else {
                console.error('Search failed:', data.error);
                return [];
            }
        })
        .catch(error => {
            console.error('Error searching items:', error);
            return [];
        });
    }
    </script>

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

        .table {
            font-size: 0.85rem;
            margin-bottom: 0;
            background: var(--bg-primary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px var(--shadow-color);
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 12px 8px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-light);
        }

        .table thead th {
            background: var(--bg-accent);
            color: #ffffff !important;
            border: none;
            font-weight: 600;
            padding: 15px 8px;
        }

        .table tbody tr {
            background: rgba(255,255,255,0.02);
            transition: background-color 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(255,255,255,0.1);
        }

        .item-code {
            font-weight: bold;
            color: var(--text-primary);
        }

        .item-name {
            font-weight: 500;
        }

        .unit-cost {
            font-weight: 600;
            color: #2e7d32;
        }

        .btn-custom {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .search-container {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .search-container:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px var(--shadow-color);
            transition: box-shadow 0.3s ease;
        }

        .table-responsive:hover {
            box-shadow: 0 6px 25px var(--shadow-color);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .form-control {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            transform: scale(1.02);
        }

        .table-container {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }


        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        body {
            color: var(--text-primary) !important;
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

        .form-control {
            border-radius: 6px;
            border: 1px solid var(--border-light);
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .form-control:focus {
            border-color: #1e40af;
            box-shadow: 0 0 0 0.15rem rgba(30, 64, 175, 0.25);
            background: var(--bg-primary);
        }

        /* Compact table styling for better fit */
        .table {
            font-size: 0.8rem;
            margin-bottom: 0;
            background: var(--bg-primary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px var(--shadow-color);
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 6px 4px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-light);
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }

        .table thead th {
            background: var(--bg-accent);
            color: #ffffff !important;
            border: none;
            font-weight: 600;
            padding: 8px 4px;
            font-size: 0.75rem;
        }

        .table tbody tr {
            background: rgba(255,255,255,0.02);
            transition: background-color 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Flexible column widths that can grow */
        .table th:nth-child(1), .table td:nth-child(1) { /* ID */
            min-width: 60px;
            width: 60px;
            text-align: center;
        }

        .table th:nth-child(2), .table td:nth-child(2) { /* Item Code */
            min-width: 100px;
        }

        .table th:nth-child(3), .table td:nth-child(3) { /* Item Name */
            min-width: 150px;
        }

        .table th:nth-child(4), .table td:nth-child(4) { /* Description */
            min-width: 200px;
        }

        .table th:nth-child(5), .table td:nth-child(5) { /* Unit */
            min-width: 70px;
            width: 80px;
        }

        .table th:nth-child(6), .table td:nth-child(6) { /* Unit Cost */
            min-width: 90px;
            width: 100px;
        }

        .table th:nth-child(7), .table td:nth-child(7) { /* Category */
            min-width: 120px;
        }

        .table th:nth-child(8), .table td:nth-child(8) { /* Actions */
            min-width: 110px;
            width: 120px;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .table th:nth-child(5), .table td:nth-child(5) { /* Description */
                min-width: 180px;
            }

            .table th:nth-child(8), .table td:nth-child(8) { /* Category */
                min-width: 100px;
            }
        }

        @media (max-width: 992px) {
            .table th:nth-child(4), .table td:nth-child(4) { /* Item Name */
                min-width: 130px;
            }

            .table th:nth-child(5), .table td:nth-child(5) { /* Description */
                min-width: 150px;
            }
        }

        @media (max-width: 768px) {
            main {
                margin-left: 0 !important;
                padding: 1rem !important;
            }

            .container-fluid {
                padding: 0.5rem;
            }

            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .d-flex.align-items-center {
                flex-direction: column;
                gap: 0.25rem;
            }

            .d-flex.align-items-center h3 {
                font-size: 1.2rem;
                margin-bottom: 0;
            }

            .d-flex.align-items-center small {
                font-size: 0.75rem;
            }

            .d-flex.gap-2 {
                width: 100%;
                justify-content: center;
                gap: 0.5rem !important;
            }

            .btn {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            .table-responsive {
                font-size: 0.7rem;
                overflow-x: auto;
            }

            .table {
                min-width: 800px; /* Ensure table doesn't get too compressed */
                font-size: 0.7rem;
            }

            .table th, .table td {
                padding: 4px 2px;
                font-size: 0.65rem;
            }

            .table thead th {
                padding: 6px 2px;
                font-size: 0.65rem;
            }

            /* Hide less important columns on mobile */
            .table th:nth-child(5), .table td:nth-child(5), /* Description */
            .table th:nth-child(8), .table td:nth-child(8) { /* Category */
                display: none;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* ID */
                min-width: 50px;
                width: 50px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* Item Code */
                min-width: 90px;
            }

            .table th:nth-child(4), .table td:nth-child(4) { /* Item Name */
                min-width: 120px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Unit */
                min-width: 55px;
                width: 60px;
            }

            .table th:nth-child(7), .table td:nth-child(7) { /* Unit Cost */
                min-width: 75px;
                width: 80px;
            }

            .table th:nth-child(9), .table td:nth-child(9) { /* Actions */
                min-width: 90px;
                width: 100px;
            }

            .btn-custom {
                font-size: 0.6rem;
                padding: 2px 4px;
            }

            .unit-cost {
                font-size: 0.65rem;
            }
        }

        @media (max-width: 576px) {
            main {
                padding: 0.25rem !important;
            }

            .container-fluid {
                padding: 0.25rem;
            }

            .d-flex.align-items-center h3 {
                font-size: 1.1rem;
            }

            .table {
                min-width: 700px;
                font-size: 0.65rem;
            }

            .table th, .table td {
                padding: 3px 1px;
                font-size: 0.6rem;
            }

            .table thead th {
                padding: 4px 1px;
                font-size: 0.6rem;
            }

            /* Further hide columns on very small screens */
            .table th:nth-child(4), .table td:nth-child(4) { /* Item Name */
                display: none;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* ID */
                min-width: 45px;
                width: 45px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* Item Code */
                min-width: 80px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Unit */
                min-width: 45px;
                width: 50px;
            }

            .table th:nth-child(7), .table td:nth-child(7) { /* Unit Cost */
                min-width: 65px;
                width: 70px;
            }

            .table th:nth-child(9), .table td:nth-child(9) { /* Actions */
                min-width: 75px;
                width: 80px;
            }

            .btn-custom {
                font-size: 0.55rem;
                padding: 1px 3px;
            }
        }

        /* DataTables specific styling */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 0.8rem;
            margin: 0.5rem 0;
        }

        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            font-size: 0.8rem;
            padding: 0.25rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>

<body class="g-sidenav-show" data-theme="light" style="background: var(--bg-primary); min-height: 100vh;">

<?php include 'sidebar.php'; ?>

<main class="main-content position-relative border-radius-lg ps ps--active-y" style="margin-left: 280px; padding: 1rem;">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <img src="assets/logo.svg" alt="PPMP Logo" style="width: 40px; height: 40px; margin-right: 10px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                <div>
                    <h4 class="text-primary mb-0" style="font-size: 1.25rem;">Database Items</h4>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        View and manage all items in the database
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <button class="btn btn-info btn-sm" id="refreshBtn" onclick="refreshData()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Custom Search Form -->
        <div class="search-container">
            <h5 class="mb-3"><i class="fas fa-search"></i> Search Items</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Item Code</label>
                    <input type="text" class="form-control" id="searchItemCode" placeholder="Enter item code">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" class="form-control" id="searchItemName" placeholder="Enter item name">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" id="searchCategory" placeholder="Enter category">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary me-2" id="searchBtn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button class="btn btn-secondary" id="clearSearchBtn">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card shadow table-container">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-striped" id="itemsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Unit Cost</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- DataTables handles pagination automatically -->
    </div>
</main>

<!-- Argon Core JS -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Token Manager for authentication - only include necessary functions -->
<script>
// Helper functions for authentication (from loginauth.js but without login-specific code)
function isLoggedIn() {
  const token = localStorage.getItem('access_token');
  // Since tokens don't expire, just check if token exists
  return !!token;
}

function getUserData() {
  const userData = localStorage.getItem('user_data');
  return userData ? JSON.parse(userData) : null;
}

function getAccessToken() {
  // First check for token in cookies (secure method)
  const cookies = document.cookie.split(';');
  for (let cookie of cookies) {
    const [name, value] = cookie.trim().split('=');
    if (name === 'auth_token') {
      return decodeURIComponent(value);
    }
  }
  // Fallback to localStorage
  return localStorage.getItem('access_token');
}
</script>

<script>
// Global error handler to suppress argon-dashboard modal errors
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes('removeChild')) {
        e.preventDefault();
        // Silently suppress the error
        return false;
    }
});

// View Database Items initialization - authentication handled by PHP
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 DEBUG: View Database Items page loaded successfully');
    console.log('🔍 DEBUG: Initializing DataTables...');
});


// DataTables handles all operations automatically
</script>

<script>
// Pass user role to JavaScript
let userRole = '<?php echo $user_role; ?>';

// Utility function to make authenticated API calls
function authenticatedFetch(url, options = {}) {
    const token = getAccessToken();

    const defaultOptions = {
        headers: {
            ...options.headers
        }
    };

    // Only set Content-Type to application/json if body is not FormData
    if (!(options.body instanceof FormData)) {
        defaultOptions.headers['Content-Type'] = 'application/json';
    }

    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }

    return fetch(url, { ...defaultOptions, ...options });
}

// Function to get current user role from token manager
function getCurrentUserRole() {
    const userData = getUserData();
    return userData ? userData.role : 'user';
}

// Theme Management
class DatabaseViewerThemeManager {
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

// Function to refresh DataTable
function refreshData() {
    const refreshBtn = document.getElementById('refreshBtn');
    if (!refreshBtn) return;

    // Disable button during refresh
    const originalText = refreshBtn.innerHTML;
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';

    try {
        // Check if DataTable exists
        const table = $('#itemsTable').DataTable();
        if (table) {
            table.ajax.reload();
            showMessage('Data refreshed successfully!', 'success');
        } else {
            console.error('DataTable not initialized');
            showMessage('Table not ready. Please reload the page.', 'warning');
        }
    } catch (error) {
        console.error('Refresh error:', error);
        showMessage('Failed to refresh data. Please try again.', 'danger');
    } finally {
        // Re-enable button
        setTimeout(() => {
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = originalText;
            }
        }, 1000);
    }
}

// DataTables handles edit actions

// DataTables handles actions

// DataTables handles error display

// Modals HTML
const modalsHTML = `
<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm">
                    <input type="hidden" id="editItemId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Item Code</label>
                            <input type="text" class="form-control" id="editItemCode" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="editItemName" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" rows="3" required></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" id="editUnit" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" class="form-control" id="editUnitCost" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" id="editCategory">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveEditBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>
`;


// Function to show messages
function showMessage(message, type) {
    // Create a temporary message container
    const container = document.createElement('div');
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(container);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        try {
            container.remove();
        } catch (error) {
            console.warn('Error removing message container:', error);
        }
    }, 5000);
}

// Edit Item Functions
document.addEventListener('DOMContentLoaded', function() {
    const saveEditBtn = document.getElementById('saveEditBtn');
    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', function() {
            const formData = {
                id: document.getElementById('editItemId').value,
                item_code: document.getElementById('editItemCode').value,
                item_name: document.getElementById('editItemName').value,
                item_description: document.getElementById('editDescription').value,
                unit: document.getElementById('editUnit').value,
                unit_cost: document.getElementById('editUnitCost').value,
                category: document.getElementById('editCategory').value
            };

            authenticatedFetch(`${API_BASE_URL}/api_update_ppmp_item.php`, {
                method: 'POST',
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    try {
                        const modalElement = document.getElementById('editItemModal');
                        if (modalElement) {
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) {
                                modal.hide();
                                // Clean up modal instance after hiding with longer delay
                                setTimeout(() => {
                                    try {
                                        if (modalElement && !modalElement.classList.contains('show')) {
                                            modal.dispose();
                                        }
                                    } catch (disposeError) {
                                        console.warn('Error disposing modal:', disposeError);
                                    }
                                }, 500); // Wait longer for hide animation and any cleanup
                            }
                        }
                    } catch (error) {
                        console.error('Error hiding modal:', error);
                        // Fallback: force hide the modal
                        try {
                            const modalElement = document.getElementById('editItemModal');
                            if (modalElement) {
                                modalElement.style.display = 'none';
                                modalElement.classList.remove('show');
                                document.body.classList.remove('modal-open');
                            }
                        } catch (fallbackError) {
                            console.warn('Fallback modal hide also failed:', fallbackError);
                        }
                    }
                    // Refresh DataTable
                    $('#itemsTable').DataTable().ajax.reload();
                } else {
                    showMessage('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error occurred.', 'danger');
            });
        });
    }
});

// Edit and Delete functions for DataTables actions
function editItem(id, code, name, description, unit, cost, category) {
    // Ensure modal exists
    const modalElement = document.getElementById('editItemModal');
    if (!modalElement) {
        console.error('Edit modal not found');
        return;
    }

    // Dispose of any existing modal instance to prevent conflicts
    try {
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }
    } catch (error) {
        console.warn('Error disposing existing modal:', error);
    }

    document.getElementById('editItemId').value = id;
    document.getElementById('editItemCode').value = code;
    document.getElementById('editItemName').value = name || '';
    document.getElementById('editDescription').value = description;
    document.getElementById('editUnit').value = unit;
    document.getElementById('editUnitCost').value = cost;
    document.getElementById('editCategory').value = category || '';

    try {
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        modal.show();
    } catch (error) {
        console.error('Error showing edit modal:', error);
    }
}

function deleteItem(itemId, itemCode) {
    if (!confirm(`Are you sure you want to delete item "${itemCode}"?`)) {
        return;
    }

    // Show loading
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    authenticatedFetch(`${API_BASE_URL}/api_delete_ppmp_item.php`, {
        method: 'POST',
        body: JSON.stringify({ id: itemId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            // Refresh DataTable
            $('#itemsTable').DataTable().ajax.reload();
        } else {
            showMessage(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showMessage('Network error. Please try again.', 'danger');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// DataTables handles all pagination and events

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables with compact settings
    console.log('🔍 DEBUG: Creating DataTables instance...');
    const table = $('#itemsTable').DataTable({
        serverSide: true,
        ajax: {
            url: API_BASE_URL + '/get_items_direct.php',
            type: 'GET',
            data: function(d) {
                d.item_code = $('#searchItemCode').val();
                d.item_name = $('#searchItemName').val();
                d.category = $('#searchCategory').val();
                console.log('🔍 DEBUG: DataTables ajax request:', {
                    url: 'get_items_direct.php',
                    draw: d.draw,
                    start: d.start,
                    length: d.length,
                    search: d.search,
                    item_code: d.item_code,
                    item_name: d.item_name,
                    category: d.category
                });
            },
            error: function(xhr, error, thrown) {
                console.error('🔍 DEBUG: DataTables ajax error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    thrown: thrown,
                    responseText: xhr.responseText ? xhr.responseText.substring(0, 200) : 'No response'
                });
            }
        },
        columns: [
            { data: 0, title: 'ID', width: '60px' }, // ID - now visible
            { data: 1, title: 'Item Code' }, // Item Code
            { data: 2, title: 'Item Name' }, // Item Name
            { data: 3, title: 'Description' }, // Description
            { data: 4, title: 'Unit' }, // Unit
            { data: 5, title: 'Unit Cost' }, // Unit Cost
            { data: 6, title: 'Category' }, // Category
            {
                data: null,
                title: 'Actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (userRole === 'admin') {
                        return `
                            <button class="btn btn-custom btn-edit me-1" onclick="editItem(${row[0]}, '${row[1].replace(/'/g, "\\'")}', '${(row[2] || '').replace(/'/g, "\\'")}', '${row[3].replace(/'/g, "\\'")}', '${row[4].replace(/'/g, "\\'")}', ${parseFloat(row[5].replace('₱', '').replace(',', ''))}, '${(row[6] || '').replace(/'/g, "\\'")}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-custom btn-delete" onclick="deleteItem(${row[0]}, '${row[1].replace(/'/g, "\\'")}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    } else {
                        return '<span class="text-muted">-</span>';
                    }
                }
            }
        ],
        pageLength: 10, // Start with fewer items per page to reduce load
        lengthMenu: [10, 25, 50, 100], // More compact options
        order: [[0, 'asc']], // Order by ID ASC
        responsive: {
            details: false // Disable responsive details to keep compact
        },
        scrollX: true, // Enable horizontal scrolling on small screens
        autoWidth: false, // Disable auto width for better control
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
            lengthMenu: '_MENU_ per page'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function(settings, json) {
            console.log('🔍 DEBUG: DataTables initialization complete');
            if (json) {
                console.log('🔍 DEBUG: Initial data loaded:', json.recordsTotal, 'total records');
            } else {
                console.log('🔍 DEBUG: No initial data received');
            }

            // Make search input smaller
            $('.dataTables_filter input').addClass('form-control-sm');
            $('.dataTables_length select').addClass('form-select-sm');
        },
        drawCallback: function(settings) {
            const info = table.page.info();
            console.log('🔍 DEBUG: Table draw complete - Showing', info.recordsDisplay, 'of', info.recordsTotal, 'records');
        }
    });

    // Initialize theme manager
    new DatabaseViewerThemeManager();

    // Insert modals into body
    document.body.insertAdjacentHTML('beforeend', modalsHTML);

    // Add event listener for refresh button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshData);
    }

    console.log('🔍 DEBUG: All initialization complete - DataTables should be loading data now');

    // Search functionality
    const searchBtn = document.getElementById('searchBtn');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    function performSearch() {
        const originalText = searchBtn.innerHTML;
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Searching...';

        table.ajax.reload(function() {
            searchBtn.disabled = false;
            searchBtn.innerHTML = originalText;
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            document.getElementById('searchItemCode').value = '';
            document.getElementById('searchItemName').value = '';
            document.getElementById('searchCategory').value = '';
            performSearch();
        });
    }

    // Allow search on Enter key
    ['searchItemCode', 'searchItemName', 'searchCategory'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    table.ajax.reload();
                }
            });
        }
    });
});
</script>

</body>
</html>
