<?php
// Items Management page - PHP-based authentication like dashboard
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
    <title>Items List | PPMP System</title>

    <!-- Argon Core CSS -->
    <link rel="stylesheet" href="argondashboard/assets/css/argon-dashboard.css">
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

        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }

        body {
            color: var(--text-primary) !important;
        }

        h1, h2, h3, h4, h5, h6, p, span, div, label {
            color: var(--text-primary);
        }

        .summary-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px var(--shadow-color);
        }

        .summary-card h3 {
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-primary);
        }

        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            color: var(--text-primary);
            background-color: var(--bg-secondary);
            border-color: var(--border-light);
        }

        .pagination .page-link:hover {
            color: var(--text-primary);
            background-color: rgba(255,255,255,0.1);
            border-color: var(--border-light);
        }

        .pagination .page-item.active .page-link {
            background: var(--bg-accent);
            border-color: var(--border-light);
            color: #ffffff;
        }

        .pagination .page-item.disabled .page-link {
            color: var(--text-muted);
            background-color: var(--bg-secondary);
            border-color: var(--border-light);
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
                    <h3 class="text-primary mb-0">Items List</h3>
                    <small class="text-muted">View all items in the system</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="showAddItemsModal()">
                    <i class="fas fa-plus"></i> Add Items
                </button>
                <button class="btn btn-outline-secondary" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <button class="btn btn-info" id="refreshBtn">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="summary-card">
                    <h3><i class="fas fa-clock text-warning"></i> Pending Items</h3>
                    <div class="summary-number text-warning" id="pendingCount">0</div>
                    <small>Awaiting approval</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="summary-card">
                    <h3><i class="fas fa-check text-success"></i> Approved Items</h3>
                    <div class="summary-number text-success" id="approvedCount">0</div>
                    <small>Ready for use</small>
                </div>
            </div>
        </div>


        <!-- Pending Items Table -->
        <div class="card shadow mb-4" id="pendingItemsCard">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Pending Items</h5>
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
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingItemsTableBody">
                            <!-- Pending items will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Approved Items Table -->
        <div class="card shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-check"></i> Approved Items</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 me-2 small">Filter by:</label>
                        <select class="form-select form-select-sm" id="dateFilter" style="width: auto;">
                            <option value="all">All Time</option>
                            <option value="day">Today</option>
                            <option value="week" selected>This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="approvedItemsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Unit Cost</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="approvedItemsTableBody">
                            <!-- Approved items will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="d-flex justify-content-between align-items-center mt-3" id="approvedPaginationContainer" style="display: none;">
                <div class="text-muted small">
                    Showing <span id="approvedStartItem">0</span> to <span id="approvedEndItem">0</span> of <span id="approvedTotalItems">0</span> items
                </div>
                <nav aria-label="Approved items pagination">
                    <ul class="pagination pagination-sm mb-0" id="approvedPaginationControls">
                        <!-- Pagination buttons will be inserted here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</main>

<!-- Modals -->
<!-- Approve/Reject Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalTitle">Review Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="approvalModalText"></p>
                <div class="mb-3">
                    <label for="adminComments" class="form-label">Comments (optional)</label>
                    <textarea class="form-control" id="adminComments" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="rejectBtn">Reject</button>
                <button type="button" class="btn btn-success" id="approveBtn">Approve</button>
            </div>
        </div>
    </div>
</div>

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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item?</p>
                <p class="text-danger" id="deleteItemInfo"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Items Modal -->
<div class="modal fade" id="addItemsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addItemsForm">
                    <div id="itemsContainer">
                        <div class="item-row mb-3 p-3 border rounded bg-light">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Item Code</label>
                                    <input type="text" class="form-control form-control-sm bg-light" placeholder="Auto-generated after approval" name="item_code[]" oninput="handleItemCodeInput(this)" autocomplete="off" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Item Name *</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="Type item name (will be UPPERCASE)" name="item_name[]" autocomplete="off" oninput="handleItemNameInput(this)" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Description *</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="Detailed description (will be Proper Case)" name="description[]" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small fw-bold">Unit *</label>
                                    <select class="form-select form-select-sm" name="unit[]" required>
                                        <option value="">Select unit</option>
                                        <option value="pcs">pcs</option>
                                        <option value="box">box</option>
                                        <option value="pack">pack</option>
                                        <option value="set">set</option>
                                        <option value="ream">ream</option>
                                        <option value="bottle">bottle</option>
                                        <option value="roll">roll</option>
                                        <option value="tube">tube</option>
                                        <option value="can">can</option>
                                        <option value="liter">liter</option>
                                        <option value="kg">kg</option>
                                        <option value="meter">meter</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Unit Cost *</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Enter price (e.g., 150.00)" name="unit_cost[]" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Category</label>
                                    <input type="text" class="form-control form-control-sm bg-light" placeholder="Other Supplies" name="category[]" value="Other Supplies" readonly>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item" style="display: none;" title="Remove this item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" id="addRowBtn"><i class="fas fa-plus"></i> Add Another Item</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success ms-2"><i class="fas fa-save"></i> Submit Items</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Argon Core JS -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/argon-dashboard.min.js"></script>


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

// Items Management initialization - authentication handled by PHP

// Utility function to make authenticated API calls
function authenticatedFetch(url, options = {}) {
  const token = getAccessToken();

  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
      ...options.headers
    }
  };

  if (token) {
    defaultOptions.headers['Authorization'] = `Bearer ${token}`;
  }

  return fetch(url, { ...defaultOptions, ...options });
}

let currentDateFilter = 'week';
let currentApprovedPage = 1;
let approvedPagination = null;

// Load pending items
function loadPendingItems() {
    const tableBody = document.getElementById('pendingItemsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading pending items...</p>
            </td>
        </tr>
    `;

    authenticatedFetch(`${API_BASE_URL}/api_get_pending_items.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPendingItems(data.pending_items || []);
            } else {
                showError('Failed to load pending items', 'pendingItemsTableBody', 10);
            }
        })
        .catch(error => {
            console.error('Error loading pending items:', error);
            showError('Network error loading pending items', 'pendingItemsTableBody', 10);
        });
}

// Load approved items
function loadApprovedItems(page = 1) {
    currentApprovedPage = page;
    const tableBody = document.getElementById('approvedItemsTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading approved items...</p>
            </td>
        </tr>
    `;

    const url = `${API_BASE_URL}/get_items.php?page=${page}&limit=50&date_filter=${currentDateFilter}`;

    authenticatedFetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.items) {
                approvedPagination = data.pagination;
                displayApprovedItems(data.items);
                renderApprovedPagination();
            } else {
                showError('Failed to load approved items', 'approvedItemsTableBody', 9);
            }
        })
        .catch(error => {
            console.error('Error loading approved items:', error);
            showError('Network error loading approved items', 'approvedItemsTableBody', 9);
        });
}

// Load all items from database
function loadItems() {
    loadPendingItems();
    loadApprovedItems(currentApprovedPage);
}


document.addEventListener('DOMContentLoaded', function() {
    console.log('Items Management page loaded successfully');

    // Load items after authentication
    loadItems();

    // Add event listener for refresh button
    document.getElementById('refreshBtn').addEventListener('click', loadItems);
});
</script>

// Pass user role from PHP to JS
let USER_ROLE = '<?php echo $user_role; ?>';
let USERNAME = '<?php echo $user; ?>';

// Function to get current username from token manager
function getCurrentUsername() {
    const userData = getUserData();
    return userData ? userData.username : '';
}

// Function to get current user role from token manager
function getCurrentUserRole() {
    const userData = getUserData();
    return userData ? userData.role : 'user';
}

// USERNAME and USER_ROLE are now set in the authentication block



// Theme Management
class ItemsListThemeManager {
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


function displayPendingItems(pendingItems) {
    const tableBody = document.getElementById('pendingItemsTableBody');

    if (pendingItems.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No pending items</h5>
                    <p class="text-muted">No items are waiting for approval.</p>
                </td>
            </tr>
        `;
        updateSummary(pendingItems.length, null);
        return;
    }

    tableBody.innerHTML = '';

    pendingItems.forEach((item, index) => {
        const formattedDate = new Date(item.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        let actions = '';
        if (USER_ROLE === 'admin') {
            actions = `
                <button class="btn btn-success btn-sm me-1" onclick="showApprovalModal(${item.id}, 'approve', '${item.item_code}')">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button class="btn btn-danger btn-sm" onclick="showApprovalModal(${item.id}, 'reject', '${item.item_code}')">
                    <i class="fas fa-times"></i> Reject
                </button>
            `;
        } else if (item.submitted_by === getCurrentUsername()) {
            actions = `
                <button class="btn btn-warning btn-sm me-1" onclick="editPendingItem(${item.id}, '${item.item_code}', '${item.item_name}', '${item.description}', '${item.unit}', ${item.unit_cost}, '${item.category}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deletePendingItem(${item.id}, '${item.item_code}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;
        } else {
            actions = '<span class="text-muted">Waiting for admin review</span>';
        }

        tableBody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.item_code}</strong></td>
                <td>${item.item_name || 'N/A'}</td>
                <td>${item.description}</td>
                <td>${item.unit}</td>
                <td>₱${parseFloat(item.unit_cost).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                <td>${item.category || 'N/A'}</td>
                <td>${item.submitted_by}</td>
                <td><small>${formattedDate}</small></td>
                <td>${actions}</td>
            </tr>
        `;
    });

    updateSummary(pendingItems.length, null);
}

function displayApprovedItems(approvedItems) {
    const tableBody = document.getElementById('approvedItemsTableBody');

    if (approvedItems.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No approved items</h5>
                    <p class="text-muted">No items have been approved yet.</p>
                </td>
            </tr>
        `;
        // Update with total count from pagination if available, otherwise 0
        const totalCount = approvedPagination ? approvedPagination.total_items : 0;
        updateSummary(null, totalCount);
        document.getElementById('approvedPaginationContainer').style.display = 'none';
        return;
    }

    tableBody.innerHTML = '';

    approvedItems.forEach((item, index) => {
        const formattedDate = new Date(item.Created_At || item.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        let actions = '';
        if (USER_ROLE === 'admin') {
            actions = `
                <button class="btn btn-warning btn-sm me-1" onclick="editItem(${item.ID}, '${item.Item_Code}', '${item.Item_Name}', '${item.Items_Description}', '${item.Unit}', ${item.Unit_Cost}, '${item.Category}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteItem(${item.ID}, '${item.Item_Code}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;
        } else {
            actions = '<span class="text-muted">No actions available</span>';
        }

        tableBody.innerHTML += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.Item_Code}</strong></td>
                <td>${item.Item_Name || 'N/A'}</td>
                <td>${item.Items_Description}</td>
                <td>${item.Unit}</td>
                <td>₱${parseFloat(item.Unit_Cost).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                <td>${item.Category || 'N/A'}</td>
                <td><small>${formattedDate}</small></td>
                <td>${actions}</td>
            </tr>
        `;
    });

    // Update with total count from pagination
    const totalCount = approvedPagination ? approvedPagination.total_items : approvedItems.length;
    updateSummary(null, totalCount);
}

function updateSummary(pendingCount, approvedCount) {
    if (pendingCount !== null) {
        document.getElementById('pendingCount').textContent = pendingCount;
    }
    if (approvedCount !== null) {
        document.getElementById('approvedCount').textContent = approvedCount;
    }
}

// Render pagination controls for approved items
function renderApprovedPagination() {
    const container = document.getElementById('approvedPaginationContainer');
    const controls = document.getElementById('approvedPaginationControls');

    if (!approvedPagination || approvedPagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'flex';

    // Update item count display
    const startItem = (approvedPagination.current_page - 1) * approvedPagination.items_per_page + 1;
    const endItem = Math.min(approvedPagination.current_page * approvedPagination.items_per_page, approvedPagination.total_items);

    document.getElementById('approvedStartItem').textContent = startItem;
    document.getElementById('approvedEndItem').textContent = endItem;
    document.getElementById('approvedTotalItems').textContent = approvedPagination.total_items;

    // Generate pagination buttons
    controls.innerHTML = '';

    // Previous button
    const prevBtn = document.createElement('li');
    prevBtn.className = `page-item ${!approvedPagination.has_prev ? 'disabled' : ''}`;
    prevBtn.innerHTML = `<a class="page-link" href="#" onclick="changeApprovedPage(${approvedPagination.current_page - 1})">Previous</a>`;
    controls.appendChild(prevBtn);

    // Page numbers
    const startPage = Math.max(1, approvedPagination.current_page - 2);
    const endPage = Math.min(approvedPagination.total_pages, approvedPagination.current_page + 2);

    if (startPage > 1) {
        const firstBtn = document.createElement('li');
        firstBtn.className = 'page-item';
        firstBtn.innerHTML = `<a class="page-link" href="#" onclick="changeApprovedPage(1)">1</a>`;
        controls.appendChild(firstBtn);

        if (startPage > 2) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = '<span class="page-link">...</span>';
            controls.appendChild(ellipsis);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('li');
        pageBtn.className = `page-item ${i === approvedPagination.current_page ? 'active' : ''}`;
        pageBtn.innerHTML = `<a class="page-link" href="#" onclick="changeApprovedPage(${i})">${i}</a>`;
        controls.appendChild(pageBtn);
    }

    if (endPage < approvedPagination.total_pages) {
        if (endPage < approvedPagination.total_pages - 1) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = '<span class="page-link">...</span>';
            controls.appendChild(ellipsis);
        }

        const lastBtn = document.createElement('li');
        lastBtn.className = 'page-item';
        lastBtn.innerHTML = `<a class="page-link" href="#" onclick="changeApprovedPage(${approvedPagination.total_pages})">${approvedPagination.total_pages}</a>`;
        controls.appendChild(lastBtn);
    }

    // Next button
    const nextBtn = document.createElement('li');
    nextBtn.className = `page-item ${!approvedPagination.has_next ? 'disabled' : ''}`;
    nextBtn.innerHTML = `<a class="page-link" href="#" onclick="changeApprovedPage(${approvedPagination.current_page + 1})">Next</a>`;
    controls.appendChild(nextBtn);
}

// Change page function
function changeApprovedPage(page) {
    if (page >= 1 && page <= approvedPagination.total_pages) {
        loadApprovedItems(page);
    }
}

function showError(message, tableBodyId, colspan) {
    const tableBody = document.getElementById(tableBodyId);
    tableBody.innerHTML = `
        <tr>
            <td colspan="${colspan}" class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Error</h5>
                <p class="text-muted">${message}</p>
            </td>
        </tr>
    `;
}

// Add Items Modal Functions
function showAddItemsModal() {
    const modal = new bootstrap.Modal(document.getElementById('addItemsModal'));
    modal.show();
}

document.getElementById('addRowBtn').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const newRow = container.querySelector('.item-row').cloneNode(true);

    // Reset all inputs and selects
    newRow.querySelectorAll('input').forEach(input => {
        input.value = '';
        // Remove datalist attribute to create new one
        input.removeAttribute('list');
        // Clear any stored properties
        delete input.lastValue;
        delete input.suggestions;
    });
    newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

    // Show the remove button for the new row
    const removeBtn = newRow.querySelector('.remove-item');
    if (removeBtn) {
        removeBtn.style.display = 'block';
    }

    container.appendChild(newRow);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
        const removeBtn = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
        removeBtn.closest('.item-row').remove();
    }
});

// Global variable to store current suggestions
let currentSuggestions = [];

// Function to handle item input and fetch suggestions
function handleItemInput(inputElement, type = 'name') {
    const term = inputElement.value.trim();
    const row = inputElement.closest('.row');

    // Create or get datalist for this row
    let datalistId = inputElement.getAttribute('list');
    if (!datalistId) {
        datalistId = 'datalist_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        inputElement.setAttribute('list', datalistId);

        // Create datalist element
        const datalist = document.createElement('datalist');
        datalist.id = datalistId;
        document.body.appendChild(datalist);
    }

    const datalist = document.getElementById(datalistId);

    if (term.length >= 2) {
        // Fetch suggestions from database
        fetch(`${API_BASE_URL}/get_items.php?suggest=1&term=${encodeURIComponent(term)}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store suggestions for this input
                    inputElement.suggestions = data.suggestions;

                    // Clear existing options
                    datalist.innerHTML = '';

                    // Add new options
                    data.suggestions.forEach(item => {
                        const option = document.createElement('option');
                        if (type === 'code') {
                            option.value = item.Item_Code;
                            option.setAttribute('data-name', item.Item_Name);
                        } else {
                            option.value = item.Item_Name;
                        }
                        option.setAttribute('data-description', item.Items_Description);
                        option.setAttribute('data-unit', item.Unit);
                        option.setAttribute('data-category', item.Category);
                        datalist.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
            });
    } else {
        // Clear suggestions if term is too short
        datalist.innerHTML = '';
        inputElement.suggestions = [];
    }

    // Store current value for comparison
    inputElement.lastValue = inputElement.value;
}

// Function to handle item name input and fetch suggestions
function handleItemNameInput(inputElement) {
    handleItemInput(inputElement, 'name');
}

// Function to handle item code input and fetch suggestions
function handleItemCodeInput(inputElement) {
    handleItemInput(inputElement, 'code');
}

// Function to generate item code based on item name
function generateItemCode(itemName, description) {
    if (!itemName || itemName.trim() === '') return '';

    // Convert to uppercase and clean
    const cleanName = itemName.toUpperCase().trim();

    // Extract first 3 letters of each major word
    const words = cleanName.split(/\s+/);
    let code = '';

    if (words.length >= 2) {
        // Take first 3 letters of first word
        code = words[0].substring(0, 3);
        // Take first 3 letters of second word
        if (words[1]) {
            code += '-' + words[1].substring(0, 3);
        }
    } else {
        // Single word - take first 6 letters
        code = words[0].substring(0, 6);
    }

    // Add description-based suffix if available
    if (description && description.trim() !== '') {
        const descWords = description.toUpperCase().split(/\s+/);
        if (descWords.length > 0) {
            const descCode = descWords[0].substring(0, 3);
            if (descCode && !code.includes(descCode)) {
                code += '-' + descCode;
            }
        }
    }

    return code;
}

// Function to convert to proper case
function toProperCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
}

// Function to handle item name input
function handleItemNameChange(inputElement) {
    const itemName = inputElement.value;
    const row = inputElement.closest('.row');
    const itemCodeField = row.querySelector('input[name="item_code[]"]');
    const descriptionField = row.querySelector('input[name="description[]"]');

    // Convert item name to uppercase
    inputElement.value = itemName.toUpperCase();

    // Item code will be generated after approval, so no auto-generation here
}

// Function to handle description input
function handleDescriptionChange(inputElement) {
    const description = inputElement.value;
    const row = inputElement.closest('.row');
    const itemNameField = row.querySelector('input[name="item_name[]"]');
    const itemCodeField = row.querySelector('input[name="item_code[]"]');

    // Convert description to proper case
    inputElement.value = toProperCase(description);

    // Item code will be generated after approval, so no auto-generation here
}

// Function to handle when user selects an item from suggestions
function handleItemSelection(inputElement) {
    const selectedValue = inputElement.value;
    const row = inputElement.closest('.row');
    const inputName = inputElement.name;

    // Find the matching suggestion from this input's suggestions
    const suggestions = inputElement.suggestions || [];
    let suggestion;

    if (inputName === 'item_code[]') {
        suggestion = suggestions.find(item => item.Item_Code === selectedValue);
    } else {
        suggestion = suggestions.find(item => item.Item_Name === selectedValue);
    }

    if (suggestion) {
        // Auto-populate fields
        const itemNameField = row.querySelector('input[name="item_name[]"]');
        const descriptionField = row.querySelector('input[name="description[]"]');
        const unitField = row.querySelector('select[name="unit[]"]');
        const categoryField = row.querySelector('select[name="category[]"]');
        const itemCodeField = row.querySelector('input[name="item_code[]"]');

        if (inputName === 'item_code[]') {
            // If selecting by code, populate name from suggestion
            if (itemNameField) itemNameField.value = suggestion.Item_Name.toUpperCase();
        } else {
            // If selecting by name, convert to uppercase
            inputElement.value = selectedValue.toUpperCase();
        }

        if (descriptionField) descriptionField.value = toProperCase(suggestion.Items_Description || '');
        if (unitField) unitField.value = suggestion.Unit || '';
        if (categoryField) categoryField.value = suggestion.Category || '';

        // Set item code if not already set
        if (itemCodeField && !itemCodeField.value.trim()) {
            if (inputName === 'item_code[]') {
                itemCodeField.value = suggestion.Item_Code;
            } else {
                // For now, leave blank - code will be generated after approval
                // const generatedCode = generateItemCode(suggestion.Item_Name, suggestion.Items_Description);
                // if (generatedCode) {
                //     itemCodeField.value = generatedCode + '-001';
                // }
            }
        }
    }
}

// Add event listeners for input changes
document.addEventListener('input', function(e) {
    if (e.target.name === 'item_name[]') {
        // Convert to uppercase in real-time
        const cursorPosition = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(cursorPosition, cursorPosition);

        // Handle both typing and selection
        if (e.inputType === 'insertReplacementText' || e.target.value !== e.target.lastValue) {
            // Small delay to allow browser to process the selection
            setTimeout(() => {
                handleItemSelection(e.target);
            }, 100);
        }
        // Store last value for comparison
        e.target.lastValue = e.target.value;

        // Generate item code in real-time
        handleItemNameChange(e.target);
    } else if (e.target.name === 'item_code[]') {
        // Handle item code input and selection
        if (e.inputType === 'insertReplacementText' || e.target.value !== e.target.lastValue) {
            // Small delay to allow browser to process the selection
            setTimeout(() => {
                handleItemSelection(e.target);
            }, 100);
        }
        // Store last value for comparison
        e.target.lastValue = e.target.value;
    } else if (e.target.name === 'description[]') {
        handleDescriptionChange(e.target);
    }
});

// Add blur event for item name to ensure final formatting
document.addEventListener('blur', function(e) {
    if (e.target.name === 'item_name[]') {
        // Ensure it's uppercase on blur as well
        e.target.value = e.target.value.toUpperCase();
        handleItemNameChange(e.target);
    }
});

document.getElementById('addItemsForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Ensure we have a valid username before submitting
    const currentUsername = getCurrentUsername();
    if (!currentUsername || currentUsername.trim() === '') {
        alert('Authentication required. Please refresh the page and try again.');
        return;
    }

    const formData = new FormData(this);
    const items = [];

    const itemCodes = formData.getAll('item_code[]');
    const itemNames = formData.getAll('item_name[]');
    const descriptions = formData.getAll('description[]');
    const units = formData.getAll('unit[]');
    const unitCosts = formData.getAll('unit_cost[]');
    const categories = formData.getAll('category[]');

    for (let i = 0; i < itemNames.length; i++) {
        if (itemNames[i].trim()) {
            items.push({
                item_code: itemCodes[i] || '', // Allow empty item_code
                item_name: itemNames[i],
                description: descriptions[i],
                unit: units[i],
                unit_cost: unitCosts[i],
                category: categories[i],
                submitted_by: currentUsername
            });
        }
    }

    if (items.length === 0) {
        alert('Please add at least one item with an item name.');
        return;
    }

    // Submit all items at once
    try {
        console.log('Submitting items to API:', items);
        const response = await fetch(`${API_BASE_URL}/api_submit_item.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAccessToken()}`
            },
            body: JSON.stringify(items)
        });
        console.log('API response status:', response.status);

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addItemsModal'));
            if (modal) modal.hide();
            // Reset form
            this.reset();
            // Reset to single row
            const container = document.getElementById('itemsContainer');
            container.innerHTML = container.querySelector('.item-row').outerHTML;
            const removeBtn = container.querySelector('.remove-item');
            if (removeBtn) removeBtn.style.display = 'none';
            loadPendingItems();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Network error:', error);
        alert('Network error occurred. Please try again.');
    }
});

// Approval Modal Functions
let currentApprovalItemId = null;
let currentApprovalAction = null;

function showApprovalModal(itemId, action, itemCode) {
    currentApprovalItemId = itemId;
    currentApprovalAction = action;

    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    const title = document.getElementById('approvalModalTitle');
    const text = document.getElementById('approvalModalText');

    if (action === 'approve') {
        title.textContent = 'Approve Item';
        text.textContent = `Are you sure you want to approve item "${itemCode}"? It will be added to the approved items list.`;
        document.getElementById('approveBtn').style.display = 'inline-block';
        document.getElementById('rejectBtn').style.display = 'none';
    } else {
        title.textContent = 'Reject Item';
        text.textContent = `Are you sure you want to reject item "${itemCode}"?`;
        document.getElementById('approveBtn').style.display = 'none';
        document.getElementById('rejectBtn').style.display = 'inline-block';
    }

    document.getElementById('adminComments').value = '';
    modal.show();
}

document.getElementById('approveBtn').addEventListener('click', function() {
    processApproval('approve');
});

document.getElementById('rejectBtn').addEventListener('click', function() {
    processApproval('reject');
});

function processApproval(action) {
    const comments = document.getElementById('adminComments').value;

    // Disable buttons to prevent multiple clicks
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    if (approveBtn) approveBtn.disabled = true;
    if (rejectBtn) rejectBtn.disabled = true;

    console.log('Sending approval request:', {
        item_id: currentApprovalItemId,
        action: action,
        admin_comments: comments,
        reviewed_by: getCurrentUsername()
    });

    fetch(`${API_BASE_URL}/api_approve_item.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAccessToken()}`
        },
        body: JSON.stringify({
            item_id: currentApprovalItemId,
            action: action,
            admin_comments: comments,
            reviewed_by: getCurrentUsername()
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        return response.text().then(text => {
            console.log('Raw response text (length:', text.length + '):', text.substring(0, 500));
            if (text.trim() === '') {
                throw new Error('Empty response from server');
            }
            try {
                const parsed = JSON.parse(text);
                console.log('Successfully parsed JSON:', parsed);
                return parsed;
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Failed to parse text:', text);
                throw new Error('Invalid JSON response: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        console.log('Parsed response data:', data);
        if (data && data.success) {
            console.log('Operation successful, showing alert and closing modal');
            alert(data.message || 'Operation completed successfully');
            try {
                const modal = bootstrap.Modal.getInstance(document.getElementById('approvalModal'));
                if (modal) {
                    modal.hide();
                }
                console.log('Modal closed, reloading data');
                loadPendingItems();
                loadApprovedItems(currentApprovedPage);
            } catch (modalError) {
                console.error('Error with modal or data reload:', modalError);
                // Still reload data even if modal fails
                loadPendingItems();
                loadApprovedItems(currentApprovedPage);
            }
        } else {
            alert('Error: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        console.error('Error details:', error.message);
        // Don't show error alert if the operation might have succeeded
        if (error.message.includes('Invalid JSON') || error.message.includes('Network')) {
            alert('Request completed but response parsing failed. Please refresh the page to see results.');
        } else {
            alert('Network error occurred: ' + error.message);
        }
    })
    .finally(() => {
        // Re-enable buttons
        if (approveBtn) approveBtn.disabled = false;
        if (rejectBtn) rejectBtn.disabled = false;
    });
}

// Edit Pending Item Functions
function editPendingItem(id, code, name, description, unit, cost, category) {
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

// Edit Approved Item Functions
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

    fetch(`${API_BASE_URL}/api_update_ppmp_item.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAccessToken()}`
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
            loadPendingItems();
            loadApprovedItems(currentApprovedPage);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred.');
    });
});

// Delete Pending Item Functions
function deletePendingItem(id, code) {
    currentDeleteItemId = id;
    document.getElementById('deleteItemInfo').textContent = `Item Code: ${code}`;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Delete Approved Item Functions
let currentDeleteItemId = null;

function deleteItem(id, code) {
    currentDeleteItemId = id;
    document.getElementById('deleteItemInfo').textContent = `Item Code: ${code}`;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch(`${API_BASE_URL}/api_delete_ppmp_item.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAccessToken()}`
        },
        body: JSON.stringify({ id: currentDeleteItemId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            loadPendingItems();
            loadApprovedItems(currentApprovedPage);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred.');
    });
});


// Date filter event listener
document.getElementById('dateFilter').addEventListener('change', function() {
    currentDateFilter = this.value;
    currentApprovedPage = 1; // Reset to first page when changing filter
    loadApprovedItems(1);
});

document.addEventListener('DOMContentLoaded', function() {
    new ItemsListThemeManager();
});
</script>

</body>
</html>
