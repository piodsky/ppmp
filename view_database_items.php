<?php
// View Database Items page - PHP-based authentication like dashboard
require_once "../apiPPMP/config.php";
require_once "../apiPPMP/token_helper.php";

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
                <button class="btn btn-info" onclick="loadItems()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-container">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by item name, description, or category..." style="max-width: 100%;">
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0">Show:</label>
                        <select class="form-select" id="itemsPerPage" style="width: auto;">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            <i class="fas fa-database me-1"></i>
                            <span id="totalCount">0</span> items
                        </span>
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
                            <!-- Items will be loaded here -->
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
  return localStorage.getItem('access_token');
}
</script>

<script>
// Helper functions for authentication
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
  return localStorage.getItem('access_token');
}
</script>

<script>
// View Database Items initialization - authentication handled by PHP
document.addEventListener('DOMContentLoaded', function() {
    console.log('View Database Items page loaded successfully');
});
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
let currentPage = 1;
let itemsPerPage = 50;
let totalPages = 1;
let totalItems = 0;
let isSearching = false;

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
        loadItems();
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

// Load items from database
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

    // Load items
    const url = `${API_BASE_URL}/get_items.php?page=1&limit=1000`;

    authenticatedFetch(url)
        .then(response => {
            console.log('Load items response status:', response.status);
            console.log('Load items response headers:', response.headers);
            return response.text().then(text => {
                console.log('Raw response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error(`Invalid JSON response: ${text.substring(0, 200)}...`);
                }
            });
        })
        .then(data => {
            console.log('Parsed load items response:', data);
            if (data.items) {
                allItems = data.items;
                filteredItems = allItems;
                totalItems = data.items.length;
                totalPages = Math.ceil(totalItems / itemsPerPage);
                currentPage = 1;

                isSearching = false;
                displayItems(allItems.slice(0, itemsPerPage));
                updatePaginationControls();
                updateTotalCount();
            } else {
                showError('Error loading items: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading items:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack,
                name: error.name
            });
            showError('Network error loading items: ' + error.message);
        });
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

    tableBody.innerHTML = '';

    items.forEach((item, index) => {
        const itemNumber = isFiltered ? index + 1 : (currentPage - 1) * itemsPerPage + index + 1;

        let actions = '';
        if (userRole === 'admin') {
            actions = `
                <button class="btn btn-custom btn-edit me-1" onclick="editItem(${item.ID}, '${item.Item_Code}', '${item.Item_Name}', '${item.Items_Description}', '${item.Unit}', ${item.Unit_Cost}, '${item.Category}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-custom btn-delete" onclick="deleteItem(${item.ID}, '${item.Item_Code}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;
        } else {
            actions = '<span class="text-muted">No actions</span>';
        }

        tableBody.innerHTML += `
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
}

function filterItems() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    if (searchTerm === '') {
        isSearching = false;
        filteredItems = allItems;
        totalPages = Math.ceil(allItems.length / itemsPerPage);
        currentPage = 1;
        displayItems(allItems.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage));
        updatePaginationControls();
        updateTotalCount();
    } else {
        isSearching = true;
        filteredItems = allItems.filter(item =>
            item.Item_Name.toLowerCase().includes(searchTerm) ||
            item.Items_Description.toLowerCase().includes(searchTerm) ||
            (item.Category && item.Category.toLowerCase().includes(searchTerm))
        );
        totalPages = Math.ceil(filteredItems.length / itemsPerPage);
        currentPage = 1;
        displayItems(filteredItems.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage), true);
        updatePaginationControls();
        updateTotalCount();
    }
}

function updateTotalCount() {
    const currentTotal = isSearching ? filteredItems.length : totalItems;
    document.getElementById('totalCount').textContent = currentTotal;
}

function editItem(id, code, name, description, unit, cost, category) {
    document.getElementById('editItemId').value = id;
    document.getElementById('editItemCode').value = code;
    document.getElementById('editItemName').value = name || '';
    document.getElementById('editDescription').value = description;
    document.getElementById('editUnit').value = unit;
    document.getElementById('editUnitCost').value = cost;
    document.getElementById('editCategory').value = category || '';

    const modal = new bootstrap.Modal(document.getElementById('editItemModal'));
    modal.show();
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

// Insert modals into body
document.body.insertAdjacentHTML('beforeend', modalsHTML);

// Edit Item Functions
document.getElementById('saveEditBtn').addEventListener('click', function() {
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
            bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
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
    // Wait for all elements to be loaded
    setTimeout(() => {
        new DatabaseViewerThemeManager();
        document.getElementById('searchInput').addEventListener('input', filterItems);
    }, 100);
});
</script>

</body>
</html>
