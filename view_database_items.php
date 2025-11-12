<?php
// View Database Items page - PHP-based authentication like dashboard
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
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/logo.svg">

    <script>
    // Define API_BASE_URL for this page
    const API_BASE_URL = "<?php echo $_ENV['API_BASE_URL'] ?? '/SystemsMISPYO/PPMP/apiPPMP'; ?>";

    // Load items via AJAX without authentication
    let preloadedItems = [];

    function loadItemsDirectly() {
        return fetch('get_items_direct.php', {
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
        return fetch(`search_items.php?${queryString}`, {
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

        /* Mobile responsiveness for View Database Items */
        @media (max-width: 1200px) {
            .table th, .table td {
                padding: 8px 4px;
                font-size: 0.85rem;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* # */
                width: 50px;
                min-width: 40px;
            }

            .table th:nth-child(2), .table td:nth-child(2) { /* Item Code */
                width: 100px;
                min-width: 80px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* Item Name */
                width: 150px;
                min-width: 120px;
            }

            .table th:nth-child(4), .table td:nth-child(4) { /* Description */
                width: 200px;
                min-width: 150px;
            }

            .table th:nth-child(5), .table td:nth-child(5) { /* Unit */
                width: 60px;
                min-width: 50px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Unit Cost */
                width: 90px;
                min-width: 80px;
            }

            .table th:nth-child(7), .table td:nth-child(7) { /* Category */
                width: 120px;
                min-width: 100px;
            }

            .table th:nth-child(8), .table td:nth-child(8) { /* Actions */
                width: 100px;
                min-width: 90px;
            }
        }

        @media (max-width: 768px) {
            main {
                margin-left: 0 !important;
                padding: 1rem !important;
            }

            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .d-flex.align-items-center {
                flex-direction: column;
                gap: 0.5rem;
            }

            .d-flex.gap-2 {
                width: 100%;
                justify-content: center;
            }

            .btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }

            .search-container {
                padding: 15px;
                margin-bottom: 15px;
            }

            .row .col-md-8, .row .col-md-4 {
                margin-bottom: 1rem;
            }

            .row .col-md-8 {
                order: 2;
            }

            .row .col-md-4 {
                order: 1;
            }

            .table-responsive {
                font-size: 0.75rem;
            }

            .table th, .table td {
                padding: 6px 3px;
                font-size: 0.75rem;
            }

            /* Hide less important columns on mobile */
            .table th:nth-child(4), .table td:nth-child(4), /* Description */
            .table th:nth-child(7), .table td:nth-child(7) { /* Category */
                display: none;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* # */
                width: 40px;
                min-width: 35px;
            }

            .table th:nth-child(2), .table td:nth-child(2) { /* Item Code */
                width: 80px;
                min-width: 70px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* Item Name */
                width: 120px;
                min-width: 100px;
            }

            .table th:nth-child(5), .table td:nth-child(5) { /* Unit */
                width: 50px;
                min-width: 45px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Unit Cost */
                width: 80px;
                min-width: 70px;
            }

            .table th:nth-child(8), .table td:nth-child(8) { /* Actions */
                width: 80px;
                min-width: 70px;
            }

            .btn-custom {
                font-size: 0.7rem;
                padding: 4px 6px;
            }

            .unit-cost {
                font-size: 0.7rem;
            }

            .pagination-container {
                margin-top: 20px;
            }

            .pagination {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 0.5rem !important;
            }

            .container-fluid {
                padding: 0;
            }

            .d-flex.justify-content-between.align-items-center.mb-4 {
                gap: 0.5rem;
            }

            .d-flex.align-items-center h3 {
                font-size: 1.3rem;
            }

            .d-flex.align-items-center small {
                font-size: 0.8rem;
            }

            .btn {
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }

            .search-container {
                margin: 0.5rem;
                padding: 12px;
                border-radius: 8px;
            }

            .form-control {
                font-size: 0.85rem;
                padding: 0.5rem;
            }

            .badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            .card {
                margin: 0.5rem;
                border-radius: 8px;
            }

            .card-body {
                padding: 1rem;
            }

            .table-responsive {
                font-size: 0.7rem;
                border-radius: 6px;
            }

            .table th, .table td {
                padding: 4px 2px;
                font-size: 0.7rem;
            }

            /* Further hide columns on very small screens */
            .table th:nth-child(3), .table td:nth-child(3), /* Item Name */
            .table th:nth-child(5), .table td:nth-child(5) { /* Unit */
                display: none;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* # */
                width: 35px;
                min-width: 30px;
            }

            .table th:nth-child(2), .table td:nth-child(2) { /* Item Code */
                width: 70px;
                min-width: 60px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Unit Cost */
                width: 75px;
                min-width: 65px;
            }

            .table th:nth-child(8), .table td:nth-child(8) { /* Actions */
                width: 70px;
                min-width: 60px;
            }

            .btn-custom {
                font-size: 0.65rem;
                padding: 3px 5px;
            }

            .unit-cost {
                font-size: 0.65rem;
            }

            .pagination {
                font-size: 0.75rem;
            }

            .page-link {
                padding: 0.25rem 0.5rem;
            }
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
                    <h3 class="text-primary mb-0">Database Items</h3>
                    <small class="text-muted">
                        View and manage all items in the database
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <button class="btn btn-info" id="refreshBtn" onclick="refreshData()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-container">
            <div class="row mb-3">
                <div class="col-md-12">
                    <h6 class="text-primary mb-3"><i class="fas fa-search me-2"></i>Search Items</h6>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Item Name</label>
                    <input type="text" id="searchItemName" class="form-control" placeholder="Enter item name...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" id="searchDescription" class="form-control" placeholder="Enter description...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select class="form-select" id="searchCategory">
                        <option value="">All Categories</option>
                        <!-- Categories will be loaded dynamically -->
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0">Show:</label>
                        <select class="form-select" id="itemsPerPage" style="width: auto;" disabled>
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            <i class="fas fa-database me-1"></i>
                            <span id="totalCount">0</span> items
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-primary" id="searchBtn">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <button class="btn btn-secondary" id="clearBtn">
                            <i class="fas fa-eraser me-1"></i>Clear
                        </button>
                        <button class="btn btn-success" id="viewAllBtn">
                            <i class="fas fa-list me-1"></i>View All Items
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped" id="itemsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Unit Cost</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                                    <h5 class="text-info">Ready to Load Items</h5>
                                    <p class="text-muted mb-3">Click "View All Items" to display all database items, or use the search box above to find specific items.</p>
                                    <small class="text-muted">Items are loaded on-demand to improve page performance.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <nav aria-label="Items pagination">
                <ul class="pagination" id="paginationControls">
                    <!-- Pagination buttons will be generated here -->
                </ul>
            </nav>
        </div>
    </div>
</main>

<!-- Argon Core JS -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

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
// View Database Items initialization - authentication handled by PHP
document.addEventListener('DOMContentLoaded', function() {
    console.log('View Database Items page loaded successfully');
    console.log('Preloaded items available:', typeof preloadedItems !== 'undefined' ? preloadedItems.length : 'undefined');
});


// Function to update total count
function updateTotalCount() {
   const currentTotal = isSearching ? filteredItems.length : totalItems;
   document.getElementById('totalCount').textContent = currentTotal;
}

// Function to enable/disable controls
function enableControls(enabled = true) {
    const itemsPerPage = document.getElementById('itemsPerPage');
    const viewAllBtn = document.getElementById('viewAllBtn');

    // Search fields are always enabled
    if (itemsPerPage) itemsPerPage.disabled = !enabled;
    if (viewAllBtn) {
        viewAllBtn.disabled = enabled; // Disable View All when items are loaded
        if (enabled) {
            viewAllBtn.innerHTML = '<i class="fas fa-check me-1"></i>Items Loaded';
            viewAllBtn.classList.remove('btn-success');
            viewAllBtn.classList.add('btn-secondary');
        } else {
            viewAllBtn.innerHTML = '<i class="fas fa-list me-1"></i>View All Items';
            viewAllBtn.classList.remove('btn-secondary');
            viewAllBtn.classList.add('btn-success');
        }
    }
}

// Function to handle View All button click
function handleViewAll() {
    const viewAllBtn = document.getElementById('viewAllBtn');

    // If items are already loaded, clear them and reset
    if (allItems.length > 0) {
        clearItems();
        return;
    }

    // Show loading state
    const tableBody = document.getElementById('itemsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading all items...</p>
            </td>
        </tr>
    `;

    // Disable button during loading
    if (viewAllBtn) {
        viewAllBtn.disabled = true;
        viewAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    }

    // Load items
    loadItemsDirectly().then(() => {
        currentMode = 'view_all';
        loadItems();
        enableControls(true);
    }).catch(error => {
        console.error('Error loading items:', error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Error Loading Items</h5>
                    <p class="text-muted">Failed to load items. Please try again.</p>
                    <button class="btn btn-primary" onclick="handleViewAll()">Retry</button>
                </td>
            </tr>
        `;
        // Reset button state
        if (viewAllBtn) {
            viewAllBtn.disabled = false;
            viewAllBtn.innerHTML = '<i class="fas fa-list me-1"></i>View All Items';
            viewAllBtn.classList.remove('btn-secondary');
            viewAllBtn.classList.add('btn-success');
        }
    });
}

// Function to clear loaded items and reset to initial state
function clearItems() {
    resetToInitialState();
}

// Function to show error messages
function showError(message) {
  const tableBody = document.getElementById('itemsTableBody');
  tableBody.innerHTML = `
    <tr>
      <td colspan="8" class="text-center py-4">
        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
        <h5 class="text-danger">Error</h5>
        <p class="text-muted">${message}</p>
      </td>
    </tr>
  `;
}

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

// Function to load categories for the dropdown
function loadCategories() {
    return fetch('get_categories.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.categories) {
            const categorySelect = document.getElementById('searchCategory');
            if (categorySelect) {
                // Clear existing options except "All Categories"
                categorySelect.innerHTML = '<option value="">All Categories</option>';

                // Add category options
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    categorySelect.appendChild(option);
                });

                console.log('Loaded', data.categories.length, 'categories');
            }
            return data.categories;
        } else {
            console.error('Failed to load categories:', data.error);
            return [];
        }
    })
    .catch(error => {
        console.error('Error loading categories:', error);
        return [];
    });
}

// Function to clean up modal instances
function cleanupModals() {
    try {
        // Only clean up if there are actual modals showing
        const visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length === 0) {
            // Clean up any lingering modal backdrops only if no modals are visible
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                try {
                    if (backdrop.parentNode) {
                        backdrop.remove();
                    }
                } catch (error) {
                    console.warn('Error removing modal backdrop:', error);
                }
            });

            // Reset body classes only if safe to do so
            if (!document.body.classList.contains('modal-open') || visibleModals.length === 0) {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }
    } catch (error) {
        console.warn('Error in modal cleanup:', error);
    }
}
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

// Global variables for items management
let allItems = [];
let filteredItems = [];
let searchResults = [];
let currentPage = 1;
let itemsPerPage = 50;
let totalPages = 1;
let totalItems = 0;
let isSearching = false;
let currentMode = 'none'; // 'none', 'view_all', 'search'
let lastSearchCriteria = {}; // Store last search criteria for refresh
let searchInProgress = false; // Prevent multiple simultaneous searches

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
        // Items will be loaded on-demand when user clicks "View All" or searches
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

// Load items from database (direct database query instead of API call)
function loadItems(page = 1) {
    currentPage = page;

    // Show loading
    const tableBody = document.getElementById('itemsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading items...</p>
            </td>
        </tr>
    `;

    // Use the pre-loaded items data instead of API call
    try {
        if (typeof preloadedItems !== 'undefined' && preloadedItems.length > 0) {
            allItems = preloadedItems;
            filteredItems = allItems;
            totalItems = allItems.length;
            totalPages = Math.ceil(totalItems / itemsPerPage);
            currentPage = 1;

            isSearching = false;
            displayItems(allItems.slice(0, itemsPerPage));
            updatePaginationControls();
            updateTotalCount();

            console.log('Items loaded successfully:', totalItems, 'items');
        } else {
            console.log('No items data available - checking if loadItemsDirectly was called');
            showError('No items data available. Please try again.');
        }
    } catch (error) {
        console.error('Error loading items:', error);
        showError('Error loading items: ' + error.message);
    }
}

function displayItems(items, isFiltered = false) {
    const tableBody = document.getElementById('itemsTableBody');

    if (items.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No items found</h5>
                    <p class="text-muted">No items match your search criteria.</p>
                </td>
            </tr>
        `;
        return;
    }

    // Build the entire table content first
    let tableContent = '';

    items.forEach((item, index) => {
        const itemNumber = isFiltered ? index + 1 : (currentPage - 1) * itemsPerPage + index + 1;

        // Escape special characters in strings to prevent issues
        const safeItemCode = (item.Item_Code || '').replace(/'/g, "\\'");
        const safeItemName = (item.Item_Name || 'N/A').replace(/'/g, "\\'");
        const safeDescription = (item.Items_Description || '').replace(/'/g, "\\'");
        const safeUnit = (item.Unit || '').replace(/'/g, "\\'");
        const safeCategory = (item.Category || 'N/A').replace(/'/g, "\\'");

        let actions = '';
        if (userRole === 'admin') {
            actions = `
                <button class="btn btn-custom btn-edit me-1" onclick="editItem(${item.ID}, '${safeItemCode}', '${safeItemName}', '${safeDescription}', '${safeUnit}', ${item.Unit_Cost}, '${safeCategory}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-custom btn-delete" onclick="deleteItem(${item.ID}, '${safeItemCode}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;
        } else {
            actions = '<span class="text-muted">No actions</span>';
        }

        tableContent += `
            <tr>
                <td>${itemNumber}</td>
                <td><span class="item-code">${item.Item_Code}</span></td>
                <td><span class="item-name">${item.Item_Name || 'N/A'}</span></td>
                <td>${item.Items_Description}</td>
                <td>${item.Unit}</td>
                <td><span class="unit-cost">₱${parseFloat(item.Unit_Cost).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span></td>
                <td>${item.Category || 'N/A'}</td>
                <td>${actions}</td>
            </tr>
        `;
    });

    // Set the table content in one operation to avoid DOM conflicts
    tableBody.innerHTML = tableContent;
}

function performSearch() {
    // Prevent multiple simultaneous searches
    if (searchInProgress) {
        console.log('Search already in progress, ignoring duplicate request');
        return;
    }

    // Get search parameters
    const itemName = document.getElementById('searchItemName').value.trim();
    const description = document.getElementById('searchDescription').value.trim();
    const category = document.getElementById('searchCategory').value.trim();

    // Check if at least one search field has content
    if (!itemName && !description && !category) {
        showMessage('Please enter at least one search criteria', 'warning');
        return;
    }

    // Set search in progress flag
    searchInProgress = true;

    // Show loading state
    const tableBody = document.getElementById('itemsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <i class="fas fa-search fa-spin fa-2x"></i>
                <p class="mt-2">Searching items...</p>
            </td>
        </tr>
    `;

    // Disable search button during search
    const searchBtn = document.getElementById('searchBtn');
    const originalText = searchBtn.innerHTML;
    searchBtn.disabled = true;
    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Searching...';

    // Perform search with retry logic
    const performSearchWithRetry = (retryCount = 0) => {
        const searchParams = {};
        if (itemName) searchParams.item_name = itemName;
        if (description) searchParams.description = description;
        if (category) searchParams.category = category;

        // Store search criteria for refresh
        lastSearchCriteria = { ...searchParams };

        // Temporarily suppress errors during search operations
        const originalOnError = window.onerror;
        let errorSuppressed = false;

        window.onerror = function(message, source, lineno, colno, error) {
            // Suppress argon-dashboard modal cleanup errors
            if (message && message.includes && message.includes('removeChild')) {
                errorSuppressed = true;
                return true;
            }
            // Call original handler for other errors
            if (originalOnError) {
                return originalOnError(message, source, lineno, colno, error);
            }
            return false;
        };

        return searchItems(searchParams).then(results => {
            if (results.length > 0) {
                currentMode = 'search';
                // Display search results
                allItems = results;
                filteredItems = results;
                totalItems = results.length;
                totalPages = Math.ceil(totalItems / itemsPerPage);
                currentPage = 1;
                isSearching = true;

                displayItems(results.slice(0, itemsPerPage), true);
                updatePaginationControls();
                updateTotalCount();
                enableControls(true);

                showMessage(`Found ${results.length} items matching your search criteria`, 'success');
            } else {
                // No results found
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Items Found</h5>
                            <p class="text-muted">No items match your search criteria. Try different keywords.</p>
                            <button class="btn btn-primary" onclick="clearSearch()">Clear Search</button>
                        </td>
                    </tr>
                `;
                updateTotalCount();
            }
    }).catch(error => {
        console.error('Search error:', error);

        // Check if this is a connection timeout and we haven't exceeded retry limit
        if (retryCount < 2 && (error.message && error.message.includes('timeout'))) {
            console.log(`Search timeout, retrying... (${retryCount + 1}/3)`);

            // Update loading message
            const tableBody = document.getElementById('itemsTableBody');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Connection timeout, retrying search... (${retryCount + 1}/3)</p>
                    </td>
                </tr>
            `;

            // Wait 1 second then retry
            setTimeout(() => {
                performSearchWithRetry(retryCount + 1);
            }, 1000);
            return;
        }

        // Final failure or non-timeout error
        const tableBody = document.getElementById('itemsTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Search Error</h5>
                    <p class="text-muted">${error.message && error.message.includes('timeout') ? 'Connection timeout. Please check your database connection.' : 'Failed to perform search. Please try again.'}</p>
                    <button class="btn btn-primary" onclick="performSearch()">Retry Search</button>
                </td>
            </tr>
        `;
    }).finally(() => {
        // Re-enable search button
        searchBtn.disabled = false;
        searchBtn.innerHTML = originalText;

        // Clear search in progress flag
        searchInProgress = false;

        // Restore original error handler
        setTimeout(() => {
            window.onerror = originalOnError;
        }, 100);
    });
    };

    // Call the retry function
    performSearchWithRetry();
}

function clearSearch() {
    // Clear search fields
    document.getElementById('searchItemName').value = '';
    document.getElementById('searchDescription').value = '';
    document.getElementById('searchCategory').value = ''; // This will select "All Categories"

    // Reset to initial "Ready to Load Items" state
    resetToInitialState();
}

// Function to refresh data based on current mode
function refreshData() {
    const refreshBtn = document.getElementById('refreshBtn');

    // Check if there's anything to refresh
    if (currentMode === 'none') {
        showMessage('Nothing to refresh. Use "View All Items" or search first.', 'info');
        return;
    }

    // Disable button during refresh
    const originalText = refreshBtn.innerHTML;
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';

    try {
        if (currentMode === 'view_all') {
            // Refresh all items
            refreshAllItems().then(() => {
                showMessage('All items refreshed successfully!', 'success');
            }).catch(error => {
                console.error('Refresh error:', error);
                showMessage('Failed to refresh items. Please try again.', 'danger');
            });
        } else if (currentMode === 'search') {
            // Re-run the last search
            refreshSearchResults().then(() => {
                showMessage('Search results refreshed successfully!', 'success');
            }).catch(error => {
                console.error('Refresh error:', error);
                showMessage('Failed to refresh search results. Please try again.', 'danger');
            });
        }
    } catch (error) {
        console.error('Refresh error:', error);
        showMessage('An error occurred during refresh.', 'danger');
    } finally {
        // Re-enable button
        setTimeout(() => {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = originalText;
        }, 1000); // Keep disabled for a moment to prevent spam clicking
    }
}

// Function to refresh all items
function refreshAllItems() {
    return new Promise((resolve, reject) => {
        // Show loading state
        const tableBody = document.getElementById('itemsTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-sync fa-spin fa-2x"></i>
                    <p class="mt-2">Refreshing all items...</p>
                </td>
            </tr>
        `;

        // Reload items from database
        loadItemsDirectly().then(() => {
            loadItems();
            resolve();
        }).catch(error => {
            reject(error);
        });
    });
}

// Function to refresh search results
function refreshSearchResults() {
    return new Promise((resolve, reject) => {
        // Check if we have search criteria
        if (!lastSearchCriteria || Object.keys(lastSearchCriteria).length === 0) {
            reject(new Error('No search criteria to refresh'));
            return;
        }

        // Show loading state
        const tableBody = document.getElementById('itemsTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-sync fa-spin fa-2x"></i>
                    <p class="mt-2">Refreshing search results...</p>
                </td>
            </tr>
        `;

        // Re-run search with last criteria
        searchItems(lastSearchCriteria).then(results => {
            if (results.length > 0) {
                allItems = results;
                filteredItems = results;
                totalItems = results.length;
                totalPages = Math.ceil(totalItems / itemsPerPage);
                currentPage = 1;

                displayItems(results.slice(0, itemsPerPage), true);
                updatePaginationControls();
                updateTotalCount();
            } else {
                // No results found
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Items Found</h5>
                            <p class="text-muted">No items match your search criteria after refresh.</p>
                        </td>
                    </tr>
                `;
                updateTotalCount();
            }
            resolve();
        }).catch(error => {
            reject(error);
        });
    });
}

function resetToInitialState() {
    // Clean up any modal instances
    cleanupModals();

    // Reset all variables
    allItems = [];
    filteredItems = [];
    searchResults = [];
    currentPage = 1;
    totalPages = 1;
    totalItems = 0;
    isSearching = false;
    currentMode = 'none';
    lastSearchCriteria = {};

    // Reset table to initial state
    const tableBody = document.getElementById('itemsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-5">
                <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                <h5 class="text-info">Ready to Load Items</h5>
                <p class="text-muted mb-3">Click "View All Items" to display all database items, or use the search box above to find specific items.</p>
                <small class="text-muted">Items are loaded on-demand to improve page performance.</small>
            </td>
        </tr>
    `;

    // Clear pagination
    const paginationControls = document.getElementById('paginationControls');
    if (paginationControls) paginationControls.innerHTML = '';

    // Reset controls to initial state
    enableControls(false);

    // Update total count
    updateTotalCount();

    // Show success message
    showMessage('Search cleared. Ready to search or load items.', 'info');
}

function updateTotalCount() {
    const currentTotal = isSearching ? filteredItems.length : totalItems;
    document.getElementById('totalCount').textContent = currentTotal;
}

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
            loadItems(); // Refresh the list
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

function showError(message) {
    const tableBody = document.getElementById('itemsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Error</h5>
                <p class="text-muted">${message}</p>
            </td>
        </tr>
    `;
}

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
        container.remove();
    }, 5000);
}

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
                    loadItems();
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

// Pagination Functions
function updatePaginationControls() {
    const paginationControls = document.getElementById('paginationControls');
    paginationControls.innerHTML = '';

    // Previous button
    const prevBtn = document.createElement('li');
    prevBtn.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevBtn.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>`;
    paginationControls.appendChild(prevBtn);

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        const firstBtn = document.createElement('li');
        firstBtn.className = 'page-item';
        firstBtn.innerHTML = `<a class="page-link" href="#" onclick="changePage(1)">1</a>`;
        paginationControls.appendChild(firstBtn);

        if (startPage > 2) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = `<span class="page-link">...</span>`;
            paginationControls.appendChild(ellipsis);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('li');
        pageBtn.className = `page-item ${i === currentPage ? 'active' : ''}`;
        pageBtn.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>`;
        paginationControls.appendChild(pageBtn);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = `<span class="page-link">...</span>`;
            paginationControls.appendChild(ellipsis);
        }

        const lastBtn = document.createElement('li');
        lastBtn.className = 'page-item';
        lastBtn.innerHTML = `<a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>`;
        paginationControls.appendChild(lastBtn);
    }

    // Next button
    const nextBtn = document.createElement('li');
    nextBtn.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextBtn.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>`;
    paginationControls.appendChild(nextBtn);
}

function changePage(page) {
    if (page >= 1 && page <= totalPages && page !== currentPage) {
        currentPage = page;
        if (isSearching) {
            displayItems(filteredItems.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage), true);
        } else {
            displayItems(allItems.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage));
        }
        updatePaginationControls();
    }
}

// Event listeners
document.getElementById('itemsPerPage').addEventListener('change', function() {
    itemsPerPage = parseInt(this.value);
    currentPage = 1; // Reset to first page when changing items per page
    if (isSearching) {
        totalPages = Math.ceil(filteredItems.length / itemsPerPage);
        displayItems(filteredItems.slice(0, itemsPerPage), true);
    } else {
        totalPages = Math.ceil(allItems.length / itemsPerPage);
        displayItems(allItems.slice(0, itemsPerPage));
    }
    updatePaginationControls();
});

document.addEventListener('DOMContentLoaded', function() {
    // Clean up any existing modals first
    cleanupModals();

    // Insert modals into body
    document.body.insertAdjacentHTML('beforeend', modalsHTML);

    // Wait for all elements to be loaded
    setTimeout(() => {
        new DatabaseViewerThemeManager();

        // Load categories for the dropdown
        loadCategories();

        // Add event listeners
        const viewAllBtn = document.getElementById('viewAllBtn');
        const searchBtn = document.getElementById('searchBtn');
        const clearBtn = document.getElementById('clearBtn');

        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', handleViewAll);
        }
        if (searchBtn) {
            searchBtn.addEventListener('click', performSearch);
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', clearSearch);
        }

        // Allow Enter key to trigger search from any input field
        ['searchItemName', 'searchDescription'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        performSearch();
                    }
                });
            }
        });

        // Allow Enter key for category select (though it's less common)
        const categorySelect = document.getElementById('searchCategory');
        if (categorySelect) {
            categorySelect.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
    }, 100);
});
</script>

</body>
</html>
