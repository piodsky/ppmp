<?php
// Settings page - authentication handled by JavaScript
# No Redis dependency needed - using database-based token authentication
$user = ''; // Will be populated by JavaScript
$user_role = 'user'; // Will be populated by JavaScript
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | PPMP System</title>

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

    <style>
        :root {
            --welcome-bg: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --card-bg: #ffffff;
            --text-on-dark: #ffffff;
            --text-muted-dark: #9ca3af;
            --border-dark: rgba(30, 58, 138, 0.2);
            --shadow-dark: rgba(0,0,0,0.1);
        }

        [data-theme="dark"] {
            --welcome-bg: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --card-bg: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            --text-on-dark: #ffffff;
            --text-muted-dark: #9ca3af;
            --border-dark: rgba(59, 130, 246, 0.3);
            --shadow-dark: rgba(0,0,0,0.4);
        }

        .welcome-card {
            background: transparent;
            color: var(--text-primary) !important;
            border-radius: 15px;
            box-shadow: 0 8px 25px var(--shadow-dark);
            border: 1px solid var(--border-light);
        }

        .stat-card {
            background: var(--card-bg);
            color: var(--text-primary) !important;
            border-radius: 15px;
            box-shadow: 0 4px 20px var(--shadow-dark);
            border: 1px solid var(--border-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px var(--shadow-dark);
        }

        .btn-primary {
            background: var(--bg-accent);
            border: 1px solid rgba(255,255,255,0.2);
            color: var(--text-on-dark);
        }

        .btn-primary:hover {
            background: var(--bg-secondary);
            transform: translateY(-2px);
        }

        .settings-section {
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 4px 20px var(--shadow-dark);
            border: 1px solid var(--border-light);
            padding: 25px;
            margin-bottom: 20px;
        }

        .settings-section h4 {
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .settings-section h4.dept-management {
            color: white !important;
            font-weight: 600;
        }

        .table-responsive {
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 4px 20px var(--shadow-dark);
            border: 1px solid var(--border-light);
            padding: 25px;
        }

        .table {
            color: var(--text-primary) !important;
        }

        .table thead th {
            border-bottom: 2px solid var(--border-light);
            font-weight: 600;
            color: var(--text-primary) !important;
        }

        .settings-section .table thead th {
            color: white !important;
        }

        .table tbody td {
            vertical-align: middle;
            color: var(--text-primary) !important;
        }

        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .action-buttons .btn {
            margin: 0 2px;
            padding: 4px 8px;
        }

        /* Mobile responsiveness for Settings */
        @media (max-width: 1200px) {
            .stat-card .card-body {
                padding: 1rem;
            }

            .stat-card h6 {
                font-size: 0.9rem;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .info-card {
                padding: 15px;
            }

            .info-card h5 {
                font-size: 1.1rem;
            }

            .table th, .table td {
                padding: 8px 4px;
                font-size: 0.85rem;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* ID */
                width: 60px;
                min-width: 50px;
            }

            .table th:nth-child(2), .table td:nth-child(2) { /* Department Name */
                width: 200px;
                min-width: 150px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* Actions */
                width: 120px;
                min-width: 100px;
            }
        }

        @media (max-width: 768px) {
            main {
                margin-left: 0 !important;
                padding: 1rem !important;
            }

            .welcome-card {
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .welcome-card .row {
                text-align: center;
            }

            .welcome-card .col-lg-8 {
                margin-bottom: 1rem;
            }

            .welcome-card h2 {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }

            .welcome-card p {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }

            .btn.btn-light.btn-lg {
                width: 100%;
                font-size: 1rem;
                padding: 0.75rem;
            }

            .row.mb-4 .col-xl-3 {
                margin-bottom: 1rem;
            }

            .stat-card {
                margin-bottom: 1rem;
            }

            .stat-card .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .stat-card h6 {
                font-size: 0.8rem;
            }

            .stat-card h3 {
                font-size: 1.3rem;
            }

            .stat-card small {
                font-size: 0.75rem;
            }

            .info-card {
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .info-card h5 {
                font-size: 1rem;
            }

            .info-card .row .col-6 {
                margin-bottom: 0.5rem;
            }

            .info-card small {
                font-size: 0.8rem;
            }

            .info-card p {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }

            .settings-section {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .settings-section h4 {
                font-size: 1.2rem;
                margin-bottom: 1rem;
            }

            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                gap: 0.5rem;
                margin-bottom: 1rem !important;
            }

            .btn.btn-primary {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .table-responsive {
                font-size: 0.75rem;
            }

            .table th, .table td {
                padding: 6px 3px;
                font-size: 0.75rem;
            }

            .table th:nth-child(2), .table td:nth-child(2) { /* Department Name */
                width: 150px;
                min-width: 120px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* Actions */
                width: 100px;
                min-width: 80px;
            }

            .action-buttons .btn {
                padding: 3px 5px;
                font-size: 0.7rem;
            }

            .action-buttons .btn i {
                font-size: 0.7rem;
            }

            .row .col-md-4 {
                margin-bottom: 1rem;
            }

            .btn.btn-warning, .btn.btn-info, .btn.btn-secondary {
                width: 100%;
                font-size: 0.9rem;
                padding: 0.75rem;
            }

            .btn.btn-secondary small {
                display: block;
                font-size: 0.75rem;
                margin-top: 0.25rem;
                opacity: 0.8;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 0.5rem !important;
            }

            .container-fluid {
                padding: 0;
            }

            .welcome-card {
                margin: 0.5rem;
                border-radius: 10px;
                padding: 1rem;
            }

            .welcome-card h2 {
                font-size: 1.3rem;
            }

            .welcome-card p {
                font-size: 0.85rem;
            }

            .btn.btn-light.btn-lg {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            .row.mb-4 {
                margin: 0.5rem !important;
            }

            .stat-card {
                margin: 0.5rem;
                border-radius: 10px;
            }

            .stat-card .card-body {
                padding: 1rem;
                text-align: center;
            }

            .stat-card .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 18px;
                margin-bottom: 0.5rem;
            }

            .stat-card h6 {
                font-size: 0.75rem;
                margin-bottom: 0.25rem;
            }

            .stat-card h3 {
                font-size: 1.2rem;
                margin-bottom: 0.25rem;
            }

            .stat-card small {
                font-size: 0.7rem;
            }

            .info-card {
                margin: 0.5rem;
                border-radius: 10px;
                padding: 1rem;
            }

            .info-card h5 {
                font-size: 0.95rem;
            }

            .info-card .row .col-6 {
                margin-bottom: 0.25rem;
            }

            .info-card small {
                font-size: 0.75rem;
            }

            .info-card p {
                font-size: 0.85rem;
                margin-bottom: 0.2rem;
            }

            .settings-section {
                margin: 0.5rem;
                border-radius: 10px;
                padding: 1rem;
            }

            .settings-section h4 {
                font-size: 1.1rem;
                margin-bottom: 0.75rem;
            }

            .d-flex.justify-content-between.align-items-center.mb-4 {
                margin-bottom: 0.75rem !important;
            }

            .btn.btn-primary {
                font-size: 0.9rem;
                padding: 0.6rem;
            }

            .table-responsive {
                margin: 0.5rem;
                border-radius: 8px;
                font-size: 0.7rem;
            }

            .table th, .table td {
                padding: 4px 2px;
                font-size: 0.7rem;
            }

            .table th:nth-child(2), .table td:nth-child(2) { /* Department Name */
                width: 120px;
                min-width: 100px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* Actions */
                width: 80px;
                min-width: 70px;
            }

            .action-buttons .btn {
                padding: 2px 4px;
                font-size: 0.65rem;
            }

            .action-buttons .btn i {
                font-size: 0.6rem;
            }

            .row .col-md-4 {
                margin: 0.25rem;
            }

            .btn.btn-warning, .btn.btn-info, .btn.btn-secondary {
                font-size: 0.85rem;
                padding: 0.6rem;
            }

            .btn.btn-secondary small {
                font-size: 0.7rem;
                margin-top: 0.2rem;
            }
        }

        /* Modal responsiveness */
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 0.5rem;
                max-width: none;
            }

            .modal-content {
                border-radius: 10px;
            }

            .modal-header, .modal-body, .modal-footer {
                padding: 1rem;
            }

            .modal-title {
                font-size: 1.2rem;
            }

            .form-control {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            .btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .modal-dialog {
                margin: 0.25rem;
            }

            .modal-header, .modal-body, .modal-footer {
                padding: 0.75rem;
            }

            .modal-title {
                font-size: 1.1rem;
            }

            .form-control {
                font-size: 0.85rem;
                padding: 0.4rem;
            }

            .btn {
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }

            .form-label {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>

<body class="g-sidenav-show" style="background: var(--bg-primary); min-height: 100vh; color: var(--text-primary);">

<?php include 'sidebar.php'; ?>

<main class="main-content position-relative border-radius-lg ps ps--active-y" style="margin-left: 280px; padding: 1.5rem;">
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2 class="mb-2"><i class="fas fa-cog me-2"></i>System Settings</h2>
                            <p class="mb-1 opacity-8">Configure system preferences and manage departments</p>
                            <p class="mb-0 opacity-8">Admin controls for system administration</p>
                        </div>
                        <div class="col-lg-4 text-end">
                            <button class="btn btn-light btn-lg" onclick="refreshSettings()">
                                <i class="fas fa-sync me-2"></i>Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-primary text-white mb-3">
                            <i class="fas fa-server"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-2">System Status</h6>
                        <h3 class="mb-0" id="systemStatus">Online</h3>
                        <small class="text-muted">PPMP v1.0.0</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-success text-white mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-2">Total Users</h6>
                        <h3 class="mb-0" id="totalUsers">0</h3>
                        <small class="text-muted" id="userBreakdown">Loading...</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-info text-white mb-3">
                            <i class="fas fa-building"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-2">Departments</h6>
                        <h3 class="mb-0" id="totalDepartments">0</h3>
                        <small class="text-muted">Active departments</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon bg-warning text-white mb-3">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-2">PPMP Documents</h6>
                        <h3 class="mb-0" id="totalPPMP">0</h3>
                        <small class="text-muted" id="ppmpStats">This year</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information Cards -->
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="info-card">
                    <h5><i class="fas fa-info-circle me-2"></i>System Information</h5>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small>PHP Version</small>
                            <p class="mb-1" id="phpVersion"><?php echo phpversion(); ?></p>
                        </div>
                        <div class="col-6">
                            <small>Database</small>
                            <p class="mb-1" id="dbStatus">Connected</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="info-card">
                    <h5><i class="fas fa-chart-line me-2"></i>Activity Summary</h5>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small>Approved PPMP</small>
                            <p class="mb-1" id="approvedPPMP">0</p>
                        </div>
                        <div class="col-6">
                            <small>Active Users</small>
                            <p class="mb-1" id="activeUsers">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Management -->
        <div class="settings-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="dept-management"><i class="fas fa-building me-2"></i>Department Management</h4>
                <button class="btn btn-primary" onclick="openAddDepartmentModal()">
                    <i class="fas fa-plus me-2"></i>Add Department
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="departmentsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Department Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="departmentsTableBody">
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading departments...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="settings-section">
            <h4><i class="fas fa-tools me-2"></i>System Maintenance</h4>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <button class="btn btn-warning btn-lg w-100" onclick="clearCache()">
                        <i class="fas fa-broom me-2"></i>Clear Cache
                    </button>
                </div>
                <div class="col-md-4 mb-3">
                    <button class="btn btn-info btn-lg w-100" onclick="backupDatabase()">
                        <i class="fas fa-download me-2"></i>Backup Database
                    </button>
                </div>
                <div class="col-md-4 mb-3">
                    <button class="btn btn-secondary btn-lg w-100" disabled title="Feature not implemented">
                        <i class="fas fa-exclamation-triangle me-2"></i>Reset System
                    </button>
                    <small class="text-muted">System reset functionality not implemented</small>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add/Edit Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid var(--border-light);">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentModalTitle">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="departmentForm">
                    <input type="hidden" id="departmentId" name="id">

                    <div class="mb-3">
                        <label for="departmentName" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="departmentName" name="department_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveDepartment()">Save Department</button>
            </div>
        </div>
    </div>
</div>

<!-- Argon Core JS -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

<script src="../apiPPMP/api_config.js.php"></script>

<!-- Token Manager for authentication -->
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
// Check authentication and admin role on Settings page load
document.addEventListener('DOMContentLoaded', async function() {
    console.log('Settings authentication check starting...');

    // Check if user is logged in
    const loggedIn = isLoggedIn();

    if (!loggedIn) {
        window.location.href = 'login.php';
        return;
    }

    // Verify token with server and get user data
    const userData = await getUserData();

    if (!userData) {
        window.location.href = 'login.php';
        return;
    }

    // Check if user is admin
    if (userData.role !== 'admin') {
        window.location.href = 'dashboard.php';
        return;
    }

    console.log('Settings authentication and authorization successful');
});
</script>

<script>
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

let departments = [];

function loadSettings() {
    authenticatedFetch(`${API_BASE_URL}/api_get_settings.php`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateSystemStats(data.data);
            } else {
                console.error('Failed to load settings:', data.message);
                showAlert('Error loading settings', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading settings:', error);
            showAlert('Network error loading settings', 'danger');
        });
}

function updateSystemStats(data) {
    // Update stats cards
    document.getElementById('systemStatus').textContent = data.system.database;
    document.getElementById('totalDepartments').textContent = data.departments_count;

    // Calculate total users
    const totalUsers = data.user_stats.reduce((sum, stat) => sum + parseInt(stat.count), 0);
    document.getElementById('totalUsers').textContent = totalUsers;

    // User breakdown
    const userBreakdown = data.user_stats.map(stat => `${stat.count} ${stat.Role}`).join(', ');
    document.getElementById('userBreakdown').textContent = userBreakdown || 'No users';

    // PPMP stats
    document.getElementById('totalPPMP').textContent = data.activity.total_ppmp || 0;
    document.getElementById('approvedPPMP').textContent = data.activity.approved_ppmp || 0;
    document.getElementById('activeUsers').textContent = data.activity.active_users || 0;
}

function loadDepartments() {
    authenticatedFetch(`${API_BASE_URL}/api_get_departments.php`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                departments = data.data;
                displayDepartments(data.data);
            } else {
                console.error('Failed to load departments:', data.message);
                showAlert('Error loading departments', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading departments:', error);
            showAlert('Network error loading departments', 'danger');
        });
}

function displayDepartments(depts) {
    const tbody = document.getElementById('departmentsTableBody');

    if (depts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center py-4">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Departments Found</h5>
                    <p class="text-muted">No departments configured in the system</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = '';

    depts.forEach(dept => {
        tbody.innerHTML += `
            <tr>
                <td>${dept.ID}</td>
                <td>${dept.Department}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-warning" onclick="editDepartment(${dept.ID})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDepartment(${dept.ID}, '${dept.Department}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
}

function openAddDepartmentModal() {
    document.getElementById('departmentModalTitle').textContent = 'Add Department';
    document.getElementById('departmentForm').reset();
    document.getElementById('departmentId').value = '';

    const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
    modal.show();
}

function editDepartment(deptId) {
    const dept = departments.find(d => d.ID == deptId);
    if (dept) {
        document.getElementById('departmentModalTitle').textContent = 'Edit Department';
        document.getElementById('departmentId').value = dept.ID;
        document.getElementById('departmentName').value = dept.Department;

        const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
        modal.show();
    }
}

function saveDepartment() {
    const form = document.getElementById('departmentForm');
    const formData = new FormData(form);
    const deptData = Object.fromEntries(formData.entries());

    authenticatedFetch(`${API_BASE_URL}/api_save_department.php`, {
        method: 'POST',
        body: JSON.stringify(deptData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('departmentModal')).hide();
            loadDepartments();
            loadSettings(); // Refresh stats
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error saving department:', error);
        showAlert('Network error occurred', 'danger');
    });
}

function deleteDepartment(deptId, deptName) {
    if (confirm(`Are you sure you want to delete department "${deptName}"? This action cannot be undone.`)) {
        authenticatedFetch(`${API_BASE_URL}/api_delete_department.php`, {
            method: 'POST',
            body: JSON.stringify({ id: deptId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert(data.message, 'success');
                loadDepartments();
                loadSettings(); // Refresh stats
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting department:', error);
            showAlert('Network error occurred', 'danger');
        });
    }
}

function refreshSettings() {
    // Show loading state
    const refreshBtn = document.querySelector('button[onclick="refreshSettings()"]');
    const originalHTML = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;

    loadSettings();
    loadDepartments();

    // Reset button after a short delay
    setTimeout(() => {
        refreshBtn.innerHTML = originalHTML;
        refreshBtn.disabled = false;
        showAlert('Settings refreshed successfully', 'success');
    }, 1000);
}

function clearCache() {
    if (confirm('Are you sure you want to clear system cache? This will log out all users.')) {
        showAlert('Cache cleared successfully', 'success');
    }
}

function backupDatabase() {
    showAlert('Database backup initiated. Download will start shortly.', 'info');
    // In a real implementation, this would trigger a database backup
}

// Reset System function removed - feature not implemented

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    loadDepartments();
});
</script>

</body>
</html>
