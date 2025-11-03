<?php
// User Management page - authentication handled by JavaScript
# No Redis dependency needed - using database-based token authentication
$user = ''; // Will be populated by JavaScript
$user_role = 'user'; // Will be populated by JavaScript
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | PPMP System</title>

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

        .table tbody td {
            vertical-align: middle;
            color: var(--text-primary) !important;
        }

        .table-responsive .table thead th {
            color: white !important;
        }

        /* Profile picture styles */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-light);
        }

        .user-avatar-small {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border-light);
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-admin { background: #dc3545; color: white; }
        .role-staff { background: #ffc107; color: black; }
        .role-user { background: #28a745; color: white; }

        .action-buttons .btn {
            margin: 0 2px;
            padding: 4px 8px;
        }

        /* Mobile responsiveness for User Management */
        @media (max-width: 1200px) {
            .table th, .table td {
                padding: 8px 4px;
                font-size: 0.85rem;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* ID */
                width: 60px;
                min-width: 50px;
            }

            .table th:nth-child(2), .table td:nth-child(2) { /* Profile */
                width: 60px;
                min-width: 50px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* First Name */
                width: 100px;
                min-width: 80px;
            }

            .table th:nth-child(4), .table td:nth-child(4) { /* Middle Name */
                width: 100px;
                min-width: 80px;
            }

            .table th:nth-child(5), .table td:nth-child(5) { /* Last Name */
                width: 100px;
                min-width: 80px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Username */
                width: 120px;
                min-width: 100px;
            }

            .table th:nth-child(7), .table td:nth-child(7) { /* Role */
                width: 80px;
                min-width: 70px;
            }

            .table th:nth-child(8), .table td:nth-child(8) { /* Department */
                width: 150px;
                min-width: 120px;
            }

            .table th:nth-child(9), .table td:nth-child(9) { /* Created Date */
                width: 120px;
                min-width: 100px;
            }

            .table th:nth-child(10), .table td:nth-child(10) { /* Actions */
                width: 100px;
                min-width: 90px;
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

            .table-responsive {
                font-size: 0.75rem;
            }

            .table th, .table td {
                padding: 6px 3px;
                font-size: 0.75rem;
            }

            /* Hide less important columns on mobile */
            .table th:nth-child(2), .table td:nth-child(2), /* Profile */
            .table th:nth-child(4), .table td:nth-child(4), /* Middle Name */
            .table th:nth-child(9), .table td:nth-child(9) { /* Created Date */
                display: none;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* ID */
                width: 50px;
                min-width: 40px;
            }

            .table th:nth-child(3), .table td:nth-child(3) { /* First Name */
                width: 80px;
                min-width: 70px;
            }

            .table th:nth-child(5), .table td:nth-child(5) { /* Last Name */
                width: 80px;
                min-width: 70px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Username */
                width: 100px;
                min-width: 80px;
            }

            .table th:nth-child(7), .table td:nth-child(7) { /* Role */
                width: 70px;
                min-width: 60px;
            }

            .table th:nth-child(8), .table td:nth-child(8) { /* Department */
                width: 120px;
                min-width: 100px;
            }

            .table th:nth-child(10), .table td:nth-child(10) { /* Actions */
                width: 80px;
                min-width: 70px;
            }

            .role-badge {
                font-size: 0.7rem;
                padding: 2px 6px;
            }

            .user-avatar, .user-avatar-small {
                width: 25px;
                height: 25px;
            }

            .action-buttons .btn {
                padding: 2px 4px;
                font-size: 0.7rem;
            }

            .action-buttons .btn i {
                font-size: 0.7rem;
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

            .table-responsive {
                margin: 0.5rem;
                border-radius: 8px;
                font-size: 0.7rem;
            }

            .table th, .table td {
                padding: 4px 2px;
                font-size: 0.7rem;
            }

            /* Further hide columns on very small screens */
            .table th:nth-child(3), .table td:nth-child(3), /* First Name */
            .table th:nth-child(5), .table td:nth-child(5) { /* Last Name */
                display: none;
            }

            .table th:nth-child(1), .table td:nth-child(1) { /* ID */
                width: 40px;
                min-width: 35px;
            }

            .table th:nth-child(6), .table td:nth-child(6) { /* Username */
                width: 90px;
                min-width: 75px;
            }

            .table th:nth-child(7), .table td:nth-child(7) { /* Role */
                width: 65px;
                min-width: 55px;
            }

            .table th:nth-child(8), .table td:nth-child(8) { /* Department */
                width: 100px;
                min-width: 85px;
            }

            .table th:nth-child(10), .table td:nth-child(10) { /* Actions */
                width: 70px;
                min-width: 60px;
            }

            .role-badge {
                font-size: 0.6rem;
                padding: 1px 3px;
            }

            .user-avatar, .user-avatar-small {
                width: 20px;
                height: 20px;
            }

            .action-buttons .btn {
                padding: 1px 3px;
                font-size: 0.65rem;
            }

            .action-buttons .btn i {
                font-size: 0.6rem;
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
                            <h2 class="mb-2"><i class="fas fa-users me-2"></i>User Management</h2>
                            <p class="mb-1 opacity-8">Manage system users, roles, and permissions</p>
                            <p class="mb-0 opacity-8">Add, edit, or remove user accounts</p>
                        </div>
                        <div class="col-lg-4 text-end">
                            <button class="btn btn-light btn-lg" onclick="openRegisterModal()">
                                <i class="fas fa-plus me-2"></i>Add New User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                    <p class="mt-2">Loading users...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid var(--border-light);">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId" name="id">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="middlename" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middlename" name="middlename">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Leave blank to keep current password (for edits)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-control" id="department" name="department_id">
                                <option value="">Select Department</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save User</button>
            </div>
        </div>
    </div>
</div>

<!-- Register New User Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid var(--border-light);">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Register New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Error Message -->
                <div class="error-message mb-3" id="registerError" style="display: none;"></div>

                <form id="registerForm">
                    <!-- Name Fields -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="regFirstname" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="regFirstname" name="firstname" placeholder="Enter first name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="regMiddlename" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="regMiddlename" name="middlename" placeholder="Enter middle name (optional)">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="regLastname" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="regLastname" name="lastname" placeholder="Enter last name" required>
                        </div>
                    </div>

                    <!-- Account Fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="regUsername" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="regUsername" name="username" placeholder="Choose a username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regRole" class="form-label">Role *</label>
                            <select class="form-control" id="regRole" name="role" required>
                                <option value="user">User</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="regDepartment" class="form-label">Department *</label>
                            <select class="form-control" id="regDepartment" name="department_id" required>
                                <option value="">Select Department</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="regPassword" class="form-label">Password *</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="regPassword" name="password" placeholder="Create a password" required>
                                <button type="button" class="password-toggle position-absolute" data-target="regPassword" style="right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted);">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="regConfirmPassword" class="form-label">Confirm Password *</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="regConfirmPassword" placeholder="Confirm your password" required>
                            <button type="button" class="password-toggle position-absolute" data-target="regConfirmPassword" style="right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted);">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Profile Picture Upload -->
                    <div class="mb-3">
                        <label class="form-label">Profile Picture (Optional)</label>
                        <div class="border rounded p-3 text-center" style="border-color: var(--border-light) !important;">
                            <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                            <p class="mb-2 text-muted">Upload a profile picture</p>
                            <input type="file" id="regProfilePicture" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('regProfilePicture').click()">
                                <i class="fas fa-upload me-2"></i>Choose File
                            </button>
                            <div id="regProfilePreview" class="mt-2" style="display: none;">
                                <img id="regProfilePreviewImg" src="" alt="Profile Preview" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-light);">
                            </div>
                        </div>
                        <small class="text-muted">JPG, PNG, GIF or WebP. Max 5MB.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="registerBtn" onclick="handleRegister()">
                    <span id="regBtnText">Create Account</span>
                    <span class="loading" id="regBtnLoading" style="display: none;"></span>
                </button>
                <button type="button" class="btn btn-success" id="updateBtn" onclick="handleRegister()" style="display: none;">
                    <span id="updateBtnText">Update User</span>
                    <span class="loading" id="updateBtnLoading" style="display: none;"></span>
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
// Check authentication and admin role on User Management page load
document.addEventListener('DOMContentLoaded', async function() {
    console.log('User Management authentication check starting...');

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

    console.log('User Management authentication and authorization successful');

    // Load data after authentication is confirmed
    loadDepartments().then(() => {
        loadUsers();
    }).catch(error => {
        console.error('Error loading departments:', error);
        loadUsers(); // Load users even if departments fail
    });
});
</script>

<script>
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

let usersTable;
let departments = [];

function loadUsers() {
    authenticatedFetch(`${API_BASE_URL}/api_get_users.php`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayUsers(data.data);
            } else {
                console.error('Failed to load users:', data.message);
                showAlert('Error loading users', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showAlert('Network error loading users', 'danger');
        });
}

function displayUsers(users) {
    const tbody = document.getElementById('usersTableBody');

    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Users Found</h5>
                    <p class="text-muted">No user accounts found in the system</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = '';

    users.forEach(user => {
        const roleClass = `role-${user.Role}`;
        const createdDate = new Date(user.Created_At).toLocaleDateString();
        const profilePic = user.profile_picture ? `uploads/profiles/${user.profile_picture}` : 'assets/logo.svg';

        tbody.innerHTML += `
            <tr>
                <td>${user.ID}</td>
                <td>
                    <img src="${profilePic}" alt="Profile" class="user-avatar-small" onerror="this.src='assets/logo.svg'">
                </td>
                <td>${user.firstname || ''}</td>
                <td>${user.middlename || ''}</td>
                <td>${user.lastname || ''}</td>
                <td>${user.Username}</td>
                <td><span class="role-badge ${roleClass}">${user.Role.toUpperCase()}</span></td>
                <td>${user.Department || 'Not Assigned'}</td>
                <td>${createdDate}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-warning" onclick="editUser(${user.ID})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.ID}, '${user.Username}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
}

function loadDepartments() {
    return authenticatedFetch(`${API_BASE_URL}/api_get_departments.php`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                departments = data.data;
                populateDepartmentSelect();
            }
        })
        .catch(error => {
            console.error('Error loading departments:', error);
        });
}

function populateDepartmentSelect() {
    const select = document.getElementById('department');
    select.innerHTML = '<option value="">Select Department</option>';

    departments.forEach(dept => {
        select.innerHTML += `<option value="${dept.ID}">${dept.Department}</option>`;
    });
}

function openAddUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('password').required = true;

    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

function openRegisterModal() {
    resetRegisterModal();
    loadRegisterDepartments();

    // Reset modal title and button for adding new user
    document.querySelector('#registerModal .modal-title').innerHTML = '<i class="fas fa-user-plus me-2"></i>Register New User';
    document.getElementById('regBtnText').textContent = 'Create Account';

    // Remove edit ID attribute
    document.getElementById('registerForm').removeAttribute('data-edit-id');

    // Show register button, hide update button
    document.getElementById('registerBtn').style.display = 'inline-block';
    document.getElementById('updateBtn').style.display = 'none';

    // Make password fields required for new users
    document.getElementById('regPassword').required = true;
    document.getElementById('regConfirmPassword').required = true;
    document.getElementById('regPassword').placeholder = 'Create a password';
    document.getElementById('regConfirmPassword').placeholder = 'Confirm your password';

    const modal = new bootstrap.Modal(document.getElementById('registerModal'));
    modal.show();
}

function loadRegisterDepartments() {
    return new Promise((resolve, reject) => {
        const departmentSelect = document.getElementById('regDepartment');
        if (departmentSelect) {
            departmentSelect.innerHTML = '<option value="">Select Department</option>';

            // Use cached departments if available, otherwise load from API
            if (departments && departments.length > 0) {
                console.log('Using cached departments:', departments);
                departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.ID;
                    option.textContent = dept.Department;
                    departmentSelect.appendChild(option);
                });
                resolve();
            } else {
                console.log('Loading departments from API');
                // Fallback: load from API if cache is empty
                authenticatedFetch(`${API_BASE_URL}/api_get_departments.php`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            departments = data.data; // Cache for future use
                            console.log('Loaded departments from API:', departments);
                            data.data.forEach(dept => {
                                const option = document.createElement('option');
                                option.value = dept.ID;
                                option.textContent = dept.Department;
                                departmentSelect.appendChild(option);
                            });
                            resolve();
                        } else {
                            reject(new Error('Failed to load departments'));
                        }
                    })
                    .catch(error => {
                        console.error('Error loading departments:', error);
                        reject(error);
                    });
            }
        } else {
            reject(new Error('Department select element not found'));
        }
    });
}

// Initialize profile picture preview and password toggles
document.addEventListener('DOMContentLoaded', function() {
    // Setup profile picture preview for register modal
    const fileInput = document.getElementById('regProfilePicture');
    const previewDiv = document.getElementById('regProfilePreview');
    const previewImg = document.getElementById('regProfilePreviewImg');

    if (fileInput && previewDiv && previewImg) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showRegisterError('Please select a valid image file (JPG, PNG, GIF, or WebP).');
                    fileInput.value = '';
                    previewDiv.style.display = 'none';
                    return;
                }

                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    showRegisterError('File size must be less than 5MB.');
                    fileInput.value = '';
                    previewDiv.style.display = 'none';
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.style.display = 'none';
            }
        });
    }

    // Setup password toggles for register modal
    setupPasswordToggles();
});

async function handleRegister() {
    console.log('handleRegister triggered - checking which button was clicked');

    const firstname = document.getElementById('regFirstname').value.trim();
    const middlename = document.getElementById('regMiddlename').value.trim();
    const lastname = document.getElementById('regLastname').value.trim();
    const username = document.getElementById('regUsername').value.trim();
    const password = document.getElementById('regPassword').value.trim();
    const confirmPassword = document.getElementById('regConfirmPassword').value.trim();
    const role = document.getElementById('regRole').value;
    const departmentId = document.getElementById('regDepartment').value;
    const profilePicture = document.getElementById('regProfilePicture').files[0];
    const editId = document.getElementById('registerForm').getAttribute('data-edit-id');
    const isEdit = editId !== null;

    console.log('handleRegister called with isEdit:', isEdit, 'editId:', editId);
    console.log('Current button visibility - registerBtn:', document.getElementById('registerBtn').style.display, 'updateBtn:', document.getElementById('updateBtn').style.display);
    console.log('Form data:', {
        firstname, middlename, lastname, username, password: password ? '[HIDDEN]' : '',
        confirmPassword: confirmPassword ? '[HIDDEN]' : '', role, departmentId,
        hasProfilePicture: !!profilePicture
    });

    // Validate inputs
    console.log('Validating inputs:', {firstname, lastname, username, departmentId});
    if (!firstname || !lastname || !username) {
        console.error('Validation failed: missing required fields');
        showRegisterError('Please fill in all required fields.');
        return;
    }

    // Department is required for new users, optional for edits
    if (!isEdit && !departmentId) {
        console.error('Validation failed: department required for new users');
        showRegisterError('Please select a department.');
        return;
    }

    // For new users, password is required
    if (!isEdit && (!password || !confirmPassword)) {
        showRegisterError('Please fill in all required fields.');
        return;
    }

    // For new users, validate password match and length
    if (!isEdit) {
        if (password !== confirmPassword) {
            showRegisterError('Passwords do not match.');
            return;
        }

        if (password.length < 6) {
            showRegisterError('Password must be at least 6 characters long.');
            return;
        }
    }

    // For editing, if password is provided, validate it
    if (isEdit && password) {
        if (password !== confirmPassword) {
            showRegisterError('Passwords do not match.');
            return;
        }

        if (password.length < 6) {
            showRegisterError('Password must be at least 6 characters long.');
            return;
        }
    }

    // Validate profile picture if selected
    if (profilePicture) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(profilePicture.type)) {
            showRegisterError('Please select a valid image file (JPG, PNG, GIF, or WebP).');
            return;
        }
        if (profilePicture.size > 5 * 1024 * 1024) {
            showRegisterError('Profile picture must be less than 5MB.');
            return;
        }
    }

    // Show loading state
    const registerBtn = document.getElementById('registerBtn');
    const btnText = document.getElementById('regBtnText');
    const btnLoading = document.getElementById('regBtnLoading');

    registerBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-block';

    try {
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('firstname', firstname);
        formData.append('middlename', middlename || '');
        formData.append('lastname', lastname);
        formData.append('username', username);
        if (password) {
            formData.append('password', password);
        }
        formData.append('role', role);
        if (departmentId) {
            formData.append('department_id', parseInt(departmentId));
        }

        if (profilePicture) {
            formData.append('profile_picture', profilePicture);
        }

        // Add edit ID if editing
        if (isEdit) {
            formData.append('id', editId);
        }

        const apiUrl = isEdit ? `${API_BASE_URL}/api_update_user.php` : `${API_BASE_URL}/api_register.php`;
        console.log('Making API call to:', apiUrl, 'with FormData');
        console.log('FormData contents:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        console.log('About to make fetch call...');
        const response = await authenticatedFetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        console.log('Fetch call completed, response status:', response.status);

        console.log('API response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('API response data:', data);

        if (data.status === 'success') {
            const successMessage = isEdit ? 'User updated successfully!' : 'User registered successfully!';
            console.log('Success:', successMessage);
            showAlert(successMessage, 'success');
            bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
            resetRegisterModal(); // Reset the modal state
            loadUsers(); // Refresh the users table
        } else {
            console.error('API error:', data.message);
            console.error('Full API response:', JSON.stringify(data, null, 2));
            showRegisterError(data.message || (isEdit ? 'Update failed. Please try again.' : 'Registration failed. Please try again.'));
        }
    } catch (error) {
        console.error('Registration/Update error:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack,
            name: error.name
        });
        showRegisterError('Network error. Please check your connection and try again.');
    } finally {
        console.log('handleRegister completed');
        // Reset loading state
        registerBtn.disabled = false;
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    }
}

function resetRegisterModal() {
    document.getElementById('registerForm').reset();
    document.getElementById('registerError').style.display = 'none';
    document.getElementById('regProfilePreview').style.display = 'none';

    // Reset button state
    const registerBtn = document.getElementById('registerBtn');
    const btnText = document.getElementById('regBtnText');
    const btnLoading = document.getElementById('regBtnLoading');
    const updateBtn = document.getElementById('updateBtn');
    const updateBtnText = document.getElementById('updateBtnText');
    const updateBtnLoading = document.getElementById('updateBtnLoading');

    registerBtn.disabled = false;
    btnText.style.display = 'inline';
    btnLoading.style.display = 'none';

    updateBtn.disabled = false;
    updateBtnText.style.display = 'inline';
    updateBtnLoading.style.display = 'none';

    // Show register button by default, hide update button
    registerBtn.style.display = 'inline-block';
    updateBtn.style.display = 'none';

    // Reset update button onclick handler
    updateBtn.onclick = null;
}

function showRegisterError(message) {
    const errorDiv = document.getElementById('registerError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function setupPasswordToggles() {
    const toggles = document.querySelectorAll('.password-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });
}

function editUser(userId) {
    console.log('editUser called with userId:', userId);
    // Find user data
    const rows = document.querySelectorAll('#usersTableBody tr');
    let userData = null;

    rows.forEach(row => {
        if (row.cells[0].textContent == userId) {
            const profileImg = row.cells[1].querySelector('img');
            const profilePicSrc = profileImg ? profileImg.src : 'assets/logo.svg';
            const profilePic = profilePicSrc.includes('uploads/profiles/') ?
                profilePicSrc.split('uploads/profiles/')[1] : null;

            userData = {
                id: row.cells[0].textContent,
                profile_picture: profilePic,
                firstname: row.cells[2].textContent,
                middlename: row.cells[3].textContent,
                lastname: row.cells[4].textContent,
                username: row.cells[5].textContent,
                role: row.cells[6].textContent.toLowerCase(),
                department: row.cells[7].textContent
            };
            console.log('Extracted userData:', userData);
        }
    });

    if (userData) {
        console.log('Populating form with userData:', userData);
        // Reset the register modal first
        resetRegisterModal();

        // Set modal title and button text for editing
        document.querySelector('#registerModal .modal-title').innerHTML = '<i class="fas fa-user-edit me-2"></i>Edit User';
        document.getElementById('regBtnText').textContent = 'Update User';

        // Show update button, hide register button
        console.log('Setting button visibility for edit mode');
        const registerBtn = document.getElementById('registerBtn');
        const updateBtn = document.getElementById('updateBtn');
        console.log('registerBtn before:', registerBtn.style.display);
        console.log('updateBtn before:', updateBtn.style.display);

        registerBtn.style.display = 'none';
        updateBtn.style.display = 'inline-block';

        console.log('registerBtn after:', registerBtn.style.display);
        console.log('updateBtn after:', updateBtn.style.display);
        console.log('updateBtn disabled:', updateBtn.disabled);

        // Ensure the update button calls handleRegister when clicked
        updateBtn.onclick = handleRegister;

        // Store user ID for editing
        document.getElementById('registerForm').setAttribute('data-edit-id', userData.id);

        // Populate form fields
        document.getElementById('regFirstname').value = userData.firstname;
        document.getElementById('regMiddlename').value = userData.middlename;
        document.getElementById('regLastname').value = userData.lastname;
        document.getElementById('regUsername').value = userData.username;
        document.getElementById('regRole').value = userData.role;

        // Handle profile picture for editing
        if (userData.profile_picture) {
            const previewImg = document.getElementById('regProfilePreviewImg');
            const previewDiv = document.getElementById('regProfilePreview');
            if (previewImg && previewDiv) {
                previewImg.src = 'uploads/profiles/' + userData.profile_picture;
                previewDiv.style.display = 'block';
            }
        }

        // Make password fields optional for editing
        document.getElementById('regPassword').required = false;
        document.getElementById('regConfirmPassword').required = false;
        document.getElementById('regDepartment').required = false;
        document.getElementById('regPassword').placeholder = 'Leave blank to keep current password';
        document.getElementById('regConfirmPassword').placeholder = 'Leave blank to keep current password';

        // Load departments, set department, and show modal
        loadRegisterDepartments().then(() => {
            console.log('Departments loaded successfully, now setting department for user');

            // Set department - need to find the department by ID, not by name
            // We need to get the department ID from the API data, not from the table display
            console.log('Setting department, userData.department:', userData.department);
            console.log('Available departments cache:', departments);

            // Find the department ID from the cached departments data
            let departmentId = null;
            if (departments && departments.length > 0) {
                const dept = departments.find(d => d.Department === userData.department);
                if (dept) {
                    departmentId = dept.ID;
                    console.log('Found department ID:', departmentId, 'for department:', userData.department);
                }
            }

            // Set the department select value
            const deptSelect = document.getElementById('regDepartment');
            if (departmentId) {
                deptSelect.value = departmentId;
                console.log('Department select set to value:', departmentId);
            } else {
                console.warn('Could not find department ID for:', userData.department);
                console.log('Available department options:', Array.from(deptSelect.options).map(opt => ({value: opt.value, text: opt.text})));
                // Show warning to user
                showAlert('Warning: Could not find department "' + userData.department + '". Please select manually.', 'warning');
            }

            console.log('Showing modal after department setup');
            const modal = new bootstrap.Modal(document.getElementById('registerModal'));
            modal.show();
        }).catch(error => {
            console.error('Error loading departments:', error);
            // Show modal even if departments fail to load
            const modal = new bootstrap.Modal(document.getElementById('registerModal'));
            modal.show();
        });
    } else {
        console.error('User data not found for userId:', userId);
    }
}

function saveUser() {
    const form = document.getElementById('userForm');
    const formData = new FormData(form);
    const userData = Object.fromEntries(formData.entries());

    // Convert department_id to number if not empty
    if (userData.department_id) {
        userData.department_id = parseInt(userData.department_id);
    } else {
        delete userData.department_id;
    }

    const isEdit = userData.id ? true : false;
    const url = isEdit ? `${API_BASE_URL}/api_update_user.php` : `${API_BASE_URL}/api_save_user.php`;

    authenticatedFetch(url, {
        method: 'POST',
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            loadUsers();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error saving user:', error);
        showAlert('Network error occurred', 'danger');
    });
}

function deleteUser(userId, username) {
    if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
        authenticatedFetch(`${API_BASE_URL}/api_delete_user.php`, {
            method: 'POST',
            body: JSON.stringify({ id: userId })
        })
        .then(response => {
            console.log('Delete user response status:', response.status);
            console.log('Delete user response headers:', response.headers);
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
            console.log('Parsed delete user response:', data);
            if (data.status === 'success') {
                showAlert(data.message, 'success');
                loadUsers();
            } else {
                showAlert(data.message || 'Failed to delete user', 'danger');
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack,
                name: error.name
            });
            showAlert('Network error occurred: ' + error.message, 'danger');
        });
    }
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Initialize is now combined above
</script>

</body>
</html>
