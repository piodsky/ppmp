<?php
// Token-based authentication - no session dependency
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
$profile_picture = $validation['profile_picture'] ?? ''; // Get profile picture from token validation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PPMP System</title>

    <!-- Argon Core CSS -->
    <link rel="stylesheet" href="argondashboard/assets/css/argon-dashboard.css">
    <!-- Nucleo Icons -->
    <link rel="stylesheet" href="argondashboard/assets/css/nucleo-icons.css">
    <link rel="stylesheet" href="argondashboard/assets/css/nucleo-svg.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/font-awesome.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/logo.svg">

    <style>
        :root {
            /* Light theme - White + Gray + Navy */
            --welcome-bg: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --card-bg: #ffffff;
            --text-on-dark: #ffffff;
            --text-muted-dark: #9ca3af;
            --border-dark: rgba(30, 58, 138, 0.2);
            --shadow-dark: rgba(0,0,0,0.1);
        }

        /* Dark theme overrides - Charcoal Gray + White + Blue */
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

        .welcome-card h2,
        .welcome-card p,
        .welcome-card .opacity-8 {
            color: var(--text-primary) !important;
        }

        .welcome-card .text-muted {
            color: var(--text-muted) !important;
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
        .stat-card .text-muted {
            color: var(--text-muted) !important;
        }
        .stat-card h6 {
            color: var(--text-muted) !important;
        }
        .stat-card h3 {
            color: var(--text-primary) !important;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: var(--bg-accent);
            border: 2px solid rgba(255,255,255,0.2);
        }
        .chart-container {
            background: var(--card-bg);
            color: var(--text-primary) !important;
            border-radius: 15px;
            box-shadow: 0 4px 20px var(--shadow-dark);
            border: 1px solid var(--border-light);
            padding: 25px;
        }
        .chart-container h5 {
            color: var(--text-primary) !important;
        }
        .recent-activity {
            background: var(--card-bg);
            color: var(--text-on-dark);
            border-radius: 15px;
            box-shadow: 0 4px 20px var(--shadow-dark);
            border: 1px solid var(--border-dark);
        }
        .activity-item {
            padding: 15px;
            border-bottom: 1px solid var(--border-dark);
            transition: background-color 0.3s ease;
        }
        .activity-item:hover {
            background-color: rgba(255,255,255,0.05);
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-item .text-muted {
            color: var(--text-muted-dark) !important;
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
        .btn-success {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-info {
            background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-warning {
            background: linear-gradient(135deg, #d69e2e 0%, #b7791f 100%);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .text-primary { color: #63b3ed !important; }
        .text-success { color: #68d391 !important; }
        .text-info { color: #4fd1c9 !important; }
        .text-warning { color: #f6e05e !important; }

        /* Department overview table styling */
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }

        #departmentTable {
            margin-bottom: 0;
        }

        #departmentTable thead th {
            border-bottom: 2px solid var(--border-light);
            font-weight: 600;
            padding: 12px 8px;
        }

        #departmentTable tbody td {
            padding: 12px 8px;
            vertical-align: middle;
        }

        #departmentTable tbody tr:hover {
            background: rgba(255,255,255,0.05);
        }

        .department-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .status-count {
            font-weight: 500;
            text-align: center;
            min-width: 60px;
        }

        .status-count.draft { color: #007bff; }
        .status-count.submitted { color: #ffc107; }
        .status-count.approved { color: #28a745; }
        .status-count.rejected { color: #dc3545; }

        .total-count {
            font-weight: 700;
            color: var(--text-primary);
            text-align: center;
        }

        /* Profile Section Styles */
        .profile-section {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-btn {
            background: transparent;
            border: none;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .profile-btn:hover {
            background: rgba(255,255,255,0.1);
            transform: scale(1.05);
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--text-on-dark);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: var(--card-bg);
            min-width: 280px;
            border-radius: 12px;
            box-shadow: 0 8px 25px var(--shadow-dark);
            border: 1px solid var(--border-light);
            z-index: 1001;
            margin-top: 8px;
        }

        .profile-dropdown-content.show {
            display: block;
        }

        .profile-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-light);
            text-align: center;
        }

        .profile-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--text-primary);
            margin: 0 auto 15px;
            display: block;
        }

        .profile-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .profile-role {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .profile-menu {
            padding: 10px 0;
        }

        .profile-menu-item {
            display: block;
            padding: 12px 20px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .profile-menu-item:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .profile-menu-item i {
            margin-right: 10px;
            width: 16px;
        }

        .profile-menu-divider {
            height: 1px;
            background: var(--border-light);
            margin: 5px 0;
        }

        /* Profile Modal Styles */
        .profile-modal .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--border-light);
        }

        .profile-modal .modal-header {
            border-bottom: 1px solid var(--border-light);
        }

        .profile-modal .modal-body {
            padding: 30px;
        }

        .profile-info-section {
            margin-bottom: 25px;
        }

        .profile-info-section h5 {
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .profile-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .profile-info-item:last-child {
            border-bottom: none;
        }

        .profile-info-label {
            font-weight: 600;
            color: var(--text-primary);
        }

        .profile-info-value {
            color: var(--text-muted);
        }

        .profile-upload-section {
            text-align: center;
            padding: 20px;
            border: 2px dashed var(--border-light);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .profile-upload-section:hover {
            border-color: var(--text-primary);
        }

        .profile-upload-btn {
            background: var(--bg-accent);
            color: var(--text-on-dark);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-upload-btn:hover {
            background: var(--bg-secondary);
            transform: translateY(-2px);
        }

        .profile-current-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--text-primary);
            margin: 0 auto 15px;
            display: block;
        }

        .welcome-profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--text-on-dark);
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .welcome-profile-avatar:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="g-sidenav-show" style="background: var(--bg-primary); min-height: 100vh; color: var(--text-primary);">

<?php include 'sidebar.php'; ?>


<main class="main-content position-relative border-radius-lg ps ps--active-y" style="margin-left: 280px; padding: 1.5rem;">
    <div class="container-fluid py-4">

        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-2 text-center">
                            <div class="profile-dropdown">
                                <img src="<?php echo !empty($profile_picture) ? 'uploads/profiles/' . htmlspecialchars($profile_picture) : 'assets/logo.svg'; ?>"
                                     alt="Profile Picture"
                                     class="welcome-profile-avatar"
                                     onclick="toggleWelcomeProfileDropdown()"
                                     onerror="this.src='assets/logo.svg'">
                                <div class="profile-dropdown-content" id="welcomeProfileDropdown">
                                    <div class="profile-menu">
                                        <button class="profile-menu-item" onclick="openProfileModal()">
                                            <i class="fas fa-user"></i>View Profile
                                        </button>
                                        <button class="profile-menu-item" onclick="openSettings()">
                                            <i class="fas fa-cog"></i>Settings
                                        </button>
                                        <div class="profile-menu-divider"></div>
                                        <button class="profile-menu-item" onclick="logout()">
                                            <i class="fas fa-sign-out-alt"></i>Logout
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <h2 class="mb-2"><i class="fas fa-chart-line me-2"></i>Welcome back, <span id="welcomeUsername">User</span>!</h2>
                            <p class="mb-1 opacity-8"><i class="fas fa-building me-2"></i><span id="welcomeDepartment">Loading...</span></p>
                            <p class="mb-0 opacity-8">Here's what's happening with your PPMP system today.</p>
                        </div>
                        <div class="col-lg-3 text-end">
                            <div class="dashboard-icon">
                                <i class="fas fa-gauge fa-3x opacity-4"></i>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted" id="lastUpdate">Loading...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-2">Total Items</h6>
                                <h3 class="mb-0" id="totalItems">0</h3>
                            </div>
                            <div class="col-auto">
                                <div class="stat-icon bg-primary text-white">
                                    <i class="fas fa-boxes"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-2">Active PPMP</h6>
                                <h3 class="mb-0" id="activePPMP">1</h3>
                            </div>
                            <div class="col-auto">
                                <div class="stat-icon bg-success text-white">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-2">Total Value</h6>
                                <h3 class="mb-0" id="totalValue">₱0.00</h3>
                            </div>
                            <div class="col-auto">
                                <div class="stat-icon bg-info text-white">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-2">This Month</h6>
                                <h3 class="mb-0" id="thisMonth">₱0.00</h3>
                            </div>
                            <div class="col-auto">
                                <div class="stat-icon bg-warning text-white">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Activity -->
        <div class="row">
            <!-- Monthly PPMP Chart -->
            <div class="col-xl-6 mb-4">
                <div class="chart-container">
                    <h5 class="mb-4"><i class="fas fa-chart-line me-2"></i>Monthly PPMP Items (<?php echo date('Y'); ?>)</h5>
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="col-xl-6 mb-4">
                <div class="chart-container">
                    <h5 class="mb-4"><i class="fas fa-chart-pie me-2"></i>PPMP Status Distribution</h5>
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Analysis and Recent Activity -->
        <div class="row">
            <!-- Top Categories -->
            <div class="col-xl-6 mb-4">
                <div class="chart-container">
                    <h5 class="mb-4"><i class="fas fa-tags me-2"></i>Top Categories by Value</h5>
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>

            <!-- Recent PPMP Activity -->
            <div class="col-xl-6 mb-4">
                <div class="recent-activity card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent PPMP Activity</h5>
                    </div>
                    <div class="card-body p-0" id="recentActivity">
                        <div class="activity-item">
                            <div class="d-flex align-items-center">
                                <div class="activity-icon bg-info text-white rounded me-3">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Loading...</small>
                                    <p class="mb-0">Loading recent activity...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department PPMP Overview -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5 class="mb-4"><i class="fas fa-building me-2"></i>Department PPMP Overview</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="departmentTable">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th class="text-center">Total PPMP</th>
                                    <th class="text-center">
                                        <span class="status-indicator bg-primary"></span> Draft
                                    </th>
                                    <th class="text-center">
                                        <span class="status-indicator bg-warning"></span> Submitted
                                    </th>
                                    <th class="text-center">
                                        <span class="status-indicator bg-success"></span> Approved
                                    </th>
                                    <th class="text-center">
                                        <span class="status-indicator bg-danger"></span> Rejected
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="departmentTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2">Loading department data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5 class="mb-4"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="ppmp.php" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-plus me-2"></i>Create PPMP
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="view_database_items.php" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-boxes me-2"></i>Manage Items
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-info btn-lg w-100" onclick="refreshStats()">
                                <i class="fas fa-sync me-2"></i>Refresh Data
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-warning btn-lg w-100" onclick="exportData()">
                                <i class="fas fa-download me-2"></i>Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Profile Modal -->
<div class="modal fade profile-modal" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>My Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="profileModalAvatar"
                              src="<?php echo !empty($profile_picture) ? 'uploads/profiles/' . htmlspecialchars($profile_picture) : 'assets/logo.svg'; ?>"
                              alt="Profile Picture"
                              class="profile-current-avatar"
                              onerror="this.src='assets/logo.svg'">
                        <div class="profile-upload-section" id="profileUploadSection" style="display: none;">
                            <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                            <p class="mb-2">Update Profile Picture</p>
                            <input type="file" id="profilePictureInput" accept="image/*" style="position: absolute; left: -9999px; top: -9999px; opacity: 0;">
                            <button type="button" class="profile-upload-btn" onclick="document.getElementById('profilePictureInput').click()">
                                <i class="fas fa-upload me-2"></i>Choose File
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="profile-info-section">
                            <h5><i class="fas fa-info-circle me-2"></i>Personal Information</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="profileFirstname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="profileFirstname" readonly required>
                                </div>
                                <div class="col-md-6">
                                    <label for="profileMiddlename" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="profileMiddlename" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="profileLastname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="profileLastname" readonly required>
                                </div>
                                <div class="col-md-6">
                                    <label for="profileNameExt" class="form-label">Name Extension</label>
                                    <input type="text" class="form-control" id="profileNameExt" readonly placeholder="Jr., Sr., III, etc.">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="profileUsername" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="profileUsername" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="profileRole" class="form-label">Role</label>
                                    <input type="text" class="form-control" id="profileRole" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="profileDepartment" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="profileDepartment" value="<?php echo htmlspecialchars($department); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="profileMemberSince" class="form-label">Member Since</label>
                                    <input type="text" class="form-control" id="profileMemberSince" value="<?php echo date('F Y', strtotime($user_data['Created_At'] ?? 'now')); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="profile-info-section">
                            <h5><i class="fas fa-shield-alt me-2"></i>Account Security</h5>
                            <div class="profile-info-item">
                                <span class="profile-info-label">Last Login:</span>
                                <span class="profile-info-value" id="lastLogin"><?php echo date('M j, Y g:i A', strtotime($user_data['Created_At'] ?? 'now')); ?></span>
                            </div>
                            <div class="profile-info-item">
                                <span class="profile-info-label">Account Status:</span>
                                <span class="profile-info-value text-success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="editProfileBtn" onclick="toggleEditMode()">
                    <i class="fas fa-edit me-2"></i>Edit Profile
                </button>
                <button type="button" class="btn btn-primary" id="saveProfileBtn" onclick="saveProfileChanges()" style="display: none;">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
                <button type="button" class="btn btn-secondary" id="cancelEditBtn" onclick="toggleEditMode()" style="display: none;">
                    <i class="fas fa-times me-2"></i>Cancel
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

<!-- Chart.js -->
<script src="assets/chart.min.js"></script>

<script src="../apiPPMP/api_config.js.php"></script>

<script>
    // Authenticated fetch function (no token refresh needed since tokens don't expire)
    function authenticatedFetch(url, options = {}) {
      const token = localStorage.getItem('access_token');
      if (!token) {
        return Promise.reject(new Error('Authentication required'));
      }

      const defaultOptions = {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          ...options.headers
        },
        ...options
      };

      return fetch(url, defaultOptions);
    }

    // Token refresh removed - tokens don't expire
</script>
<script>
// Dashboard initialization - session-based authentication
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard loaded successfully');

    // Populate user data from PHP session variables
    const userData = {
        username: '<?php echo htmlspecialchars($username); ?>',
        firstname: '<?php echo htmlspecialchars($firstname); ?>',
        lastname: '<?php echo htmlspecialchars($lastname); ?>',
        role: '<?php echo htmlspecialchars($role); ?>',
        department: '<?php echo htmlspecialchars($department); ?>'
    };

    populateUserData(userData);
});

// Helper function to populate user data in UI
function populateUserData(userData) {
    // Update welcome message
    const welcomeUsername = document.getElementById('welcomeUsername');
    const welcomeDepartment = document.getElementById('welcomeDepartment');
    if (welcomeUsername) {
        welcomeUsername.textContent = userData.username || userData.Username || 'User';
    }
    if (welcomeDepartment) {
        welcomeDepartment.textContent = userData.department || userData.Department || 'Not Assigned';
    }

    // Update profile picture
    const profileImg = document.querySelector('.welcome-profile-avatar');
    if (profileImg && userData.profile_picture) {
        profileImg.src = 'uploads/profiles/' + userData.profile_picture;
        profileImg.onerror = function() { this.src = 'assets/logo.svg'; };
    }

    console.log('Dashboard authentication successful');
}
</script>

<script>
let monthlyChart, statusChart, categoryChart;

function loadDashboardStats() {
    // Show loading indicator
    const lastUpdateEl = document.getElementById('lastUpdate');
    lastUpdateEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    authenticatedFetch(`${API_BASE_URL}/api_dashboard_stats.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update statistics cards
                document.getElementById('totalItems').textContent = data.stats.total_items;
                document.getElementById('activePPMP').textContent = data.stats.total_ppmp;
                document.getElementById('totalValue').textContent = '₱' + data.stats.total_value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('thisMonth').textContent = '₱' + data.stats.month_value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                // Update charts with real data
                updateMonthlyChart(data.monthly_data);
                updateStatusChart(data.status_distribution);
                updateCategoryChart(data.category_data);
                updateRecentActivity(data.recent_ppmp);
                updateDepartmentOverview(data.department_data);

                // Update last update time
                const now = new Date();
                lastUpdateEl.innerHTML = '<i class="fas fa-clock"></i> Updated ' + now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            } else {
                console.error('Failed to load dashboard data:', data.message);
                lastUpdateEl.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i> Update failed';
            }
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
            lastUpdateEl.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i> Connection error';
        });
}

function initializeCharts() {
    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'PPMP Items',
                data: [],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#007bff', '#ffc107', '#28a745', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Value (₱)',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function updateMonthlyChart(data) {
    if (monthlyChart) {
        monthlyChart.data.datasets[0].data = data;
        monthlyChart.update();
    }
}

function updateStatusChart(data) {
    if (statusChart) {
        const labels = data.map(item => item.Status || 'Unknown');
        const values = data.map(item => item.count);
        statusChart.data.labels = labels;
        statusChart.data.datasets[0].data = values;
        statusChart.update();
    }
}

function updateCategoryChart(data) {
    if (categoryChart) {
        const labels = data.map(item => item.Category || 'Unknown');
        const values = data.map(item => item.total_value || 0);
        categoryChart.data.labels = labels;
        categoryChart.data.datasets[0].data = values;
        categoryChart.update();
    }
}

function updateRecentActivity(data) {
    const activityContainer = document.getElementById('recentActivity');

    if (data.length === 0) {
        activityContainer.innerHTML = `
            <div class="activity-item">
                <div class="d-flex align-items-center">
                    <div class="activity-icon bg-secondary text-white rounded me-3">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div>
                        <small class="text-muted">No recent activity</small>
                        <p class="mb-0">No PPMP documents found</p>
                    </div>
                </div>
            </div>
        `;
        return;
    }

    activityContainer.innerHTML = '';

    data.forEach(ppmp => {
        const date = new Date(ppmp.Created_At).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const statusClass = getStatusClass(ppmp.Status);
        const statusIcon = getStatusIcon(ppmp.Status);

        activityContainer.innerHTML += `
            <div class="activity-item">
                <div class="d-flex align-items-center">
                    <div class="activity-icon ${statusClass} text-white rounded me-3">
                        <i class="${statusIcon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <small class="text-muted">${date}</small>
                        <p class="mb-0">${ppmp.PPMP_Number} (${ppmp.Department}) - ${ppmp.Status}</p>
                        <small class="text-muted">by ${ppmp.Created_By}</small>
                    </div>
                </div>
            </div>
        `;
    });
}

function updateDepartmentOverview(data) {
    const tableBody = document.getElementById('departmentTableBody');

    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Department Data</h5>
                    <p class="text-muted">No PPMP documents found for any department</p>
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = '';

    data.forEach(dept => {
        const totalPPMP = parseInt(dept.total_ppmp) || 0;
        const draftCount = parseInt(dept.draft_count) || 0;
        const submittedCount = parseInt(dept.submitted_count) || 0;
        const approvedCount = parseInt(dept.approved_count) || 0;
        const rejectedCount = parseInt(dept.rejected_count) || 0;

        tableBody.innerHTML += `
            <tr>
                <td>
                    <div class="department-name">
                        <i class="fas fa-building me-2"></i>${dept.department}
                    </div>
                </td>
                <td class="total-count">${totalPPMP}</td>
                <td class="status-count draft">${draftCount}</td>
                <td class="status-count submitted">${submittedCount}</td>
                <td class="status-count approved">${approvedCount}</td>
                <td class="status-count rejected">${rejectedCount}</td>
            </tr>
        `;
    });
}

function getStatusClass(status) {
    switch (status?.toLowerCase()) {
        case 'draft': return 'bg-primary';
        case 'submitted': return 'bg-warning';
        case 'approved': return 'bg-success';
        default: return 'bg-secondary';
    }
}

function getStatusIcon(status) {
    switch (status?.toLowerCase()) {
        case 'saved': return 'fas fa-save';
        case 'draft': return 'fas fa-edit';
        case 'submitted': return 'fas fa-paper-plane';
        default: return 'fas fa-question';
    }
}

function refreshStats() {
    // Show loading state on button
    const refreshBtn = document.querySelector('button[onclick="refreshStats()"]');
    const originalHTML = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;

    loadDashboardStats();

    // Show success message after a short delay
    setTimeout(() => {
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>Dashboard data refreshed successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);

        // Reset button
        refreshBtn.innerHTML = originalHTML;
        refreshBtn.disabled = false;

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }, 1000);
}

function exportData() {
    // Export dashboard data as JSON
    authenticatedFetch(`${API_BASE_URL}/api_dashboard_stats.php`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const exportData = {
                    exported_at: new Date().toISOString(),
                    stats: data.stats,
                    monthly_data: data.monthly_data,
                    status_distribution: data.status_distribution,
                    category_data: data.category_data,
                    recent_ppmp: data.recent_ppmp
                };

                const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `ppmp_dashboard_${new Date().toISOString().split('T')[0]}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        })
        .catch(error => {
            console.error('Export error:', error);
            alert('Export failed. Please try again.');
        });
}

// Profile functionality
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');

        // Close dropdown when clicking outside
        document.addEventListener('click', function closeDropdown(e) {
            if (!e.target.closest('.profile-dropdown')) {
                dropdown.classList.remove('show');
                document.removeEventListener('click', closeDropdown);
            }
        });
    }
}

function toggleWelcomeProfileDropdown() {
    const dropdown = document.getElementById('welcomeProfileDropdown');
    dropdown.classList.toggle('show');

    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!e.target.closest('.profile-dropdown')) {
            dropdown.classList.remove('show');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

function openProfileModal() {
    const modal = new bootstrap.Modal(document.getElementById('profileModal'));
    modal.show();
    const profileDropdown = document.getElementById('profileDropdown');
    const welcomeProfileDropdown = document.getElementById('welcomeProfileDropdown');
    if (profileDropdown) profileDropdown.classList.remove('show');
    if (welcomeProfileDropdown) welcomeProfileDropdown.classList.remove('show');

    // Populate profile data from PHP session variables
    document.getElementById('profileFirstname').value = '<?php echo htmlspecialchars($firstname); ?>';
    document.getElementById('profileLastname').value = '<?php echo htmlspecialchars($lastname); ?>';
    document.getElementById('profileUsername').value = '<?php echo htmlspecialchars($username); ?>';
    document.getElementById('profileRole').value = '<?php echo htmlspecialchars($role); ?>';
    document.getElementById('profileDepartment').value = '<?php echo htmlspecialchars($department); ?>';

    // Update profile picture
    const profileImg = document.getElementById('profileModalAvatar');
    if (profileImg) {
        profileImg.src = '<?php echo !empty($profile_picture) ? "uploads/profiles/" . htmlspecialchars($profile_picture) : "assets/logo.svg"; ?>';
        profileImg.onerror = function() { this.src = 'assets/logo.svg'; };
    }

    // Initialize profile picture preview
    initializeProfilePicturePreview();

    // Reset to view mode
    setViewMode();
}

function openSettings() {
    window.location.href = 'settings.php';
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

function setViewMode() {
    // Make all inputs readonly
    const inputs = document.querySelectorAll('#profileModal input[type="text"]');
    inputs.forEach(input => {
        if (input.id !== 'profileUsername' && input.id !== 'profileRole' && input.id !== 'profileDepartment' && input.id !== 'profileMemberSince') {
            input.readOnly = true;
        }
    });

    // Hide upload section
    document.getElementById('profileUploadSection').style.display = 'none';

    // Remove onclick from avatar
    document.getElementById('profileModalAvatar').onclick = null;
    document.getElementById('profileModalAvatar').style.cursor = 'default';

    // Show edit button, hide save and cancel
    document.getElementById('editProfileBtn').style.display = 'inline-block';
    document.getElementById('saveProfileBtn').style.display = 'none';
    document.getElementById('cancelEditBtn').style.display = 'none';
}

function toggleEditMode() {
    const editBtn = document.getElementById('editProfileBtn');
    const saveBtn = document.getElementById('saveProfileBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const uploadSection = document.getElementById('profileUploadSection');
    const avatar = document.getElementById('profileModalAvatar');

    if (editBtn.style.display !== 'none') {
        // Switch to edit mode
        const inputs = document.querySelectorAll('#profileModal input[type="text"]');
        inputs.forEach(input => {
            if (input.id !== 'profileUsername' && input.id !== 'profileRole' && input.id !== 'profileDepartment' && input.id !== 'profileMemberSince') {
                input.readOnly = false;
            }
        });

        // Show upload section
        uploadSection.style.display = 'block';

        // Make avatar clickable
        avatar.onclick = () => document.getElementById('profilePictureInput').click();
        avatar.style.cursor = 'pointer';

        // Hide edit, show save and cancel
        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
        cancelBtn.style.display = 'inline-block';
    } else {
        // Switch back to view mode
        setViewMode();
    }
}

function loadLastLoginInfo() {
    // Fetch last login info from server
    authenticatedFetch(`${API_BASE_URL}/api_get_user_info.php`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const createdAt = new Date(data.user.Created_At);
                document.getElementById('lastLogin').textContent = createdAt.toLocaleString();
            } else {
                document.getElementById('lastLogin').textContent = 'Unknown';
            }
        })
        .catch(error => {
            console.error('Error loading last login info:', error);
            document.getElementById('lastLogin').textContent = 'Error loading';
        });
}

function saveProfileChanges() {
    const fileInput = document.getElementById('profilePictureInput');
    const file = fileInput.files[0];

    // Get name field values
    const firstname = document.getElementById('profileFirstname').value.trim();
    const middlename = document.getElementById('profileMiddlename').value.trim();
    const lastname = document.getElementById('profileLastname').value.trim();
    const nameExt = document.getElementById('profileNameExt').value.trim();

    // Validate required fields
    if (!firstname || !lastname) {
        alert('First name and last name are required.');
        return;
    }

    // Show loading
    const saveBtn = document.querySelector('#profileModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    saveBtn.disabled = true;

    // Create FormData for both profile picture and name data
    const formData = new FormData();

    // Add name fields
    formData.append('firstname', firstname);
    formData.append('middlename', middlename || '');
    formData.append('lastname', lastname);
    formData.append('name_ext', nameExt || '');

    // Add profile picture if selected
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, GIF, or WebP).');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB.');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
            return;
        }

        formData.append('profile_picture', file);
    }

    // Save profile data
    authenticatedFetch(`${API_BASE_URL}/api_update_profile.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update profile pictures in the UI if a new picture was uploaded
            if (file && data.filename) {
                const newImageUrl = 'uploads/profiles/' + data.filename;
                document.querySelectorAll('.profile-avatar, .profile-avatar-large, .profile-current-avatar, .welcome-profile-avatar').forEach(img => {
                    img.src = newImageUrl;
                });
            }

            alert('Profile updated successfully!');

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();

            // Clear file input
            if (fileInput) fileInput.value = '';
        } else {
            alert('Update failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        alert('Update failed. Please try again.');
    })
    .finally(() => {
        // Reset button
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Profile picture preview functionality
function initializeProfilePicturePreview() {
    const fileInput = document.getElementById('profilePictureInput');
    const previewImg = document.getElementById('profileModalAvatar');

    if (fileInput && previewImg) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, or WebP).');
                    fileInput.value = '';
                    return;
                }

                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB.');
                    fileInput.value = '';
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    alert('Profile picture preview updated! The changes will be saved when you click Save Changes.');
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.profile-dropdown')) {
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) profileDropdown.classList.remove('show');
    }
});

// Initialize everything when DOM loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadDashboardStats();

    // Auto-refresh dashboard data every 30 seconds
    setInterval(() => {
        if (document.hasFocus()) {
            loadDashboardStats();
        }
    }, 30000); // 30 seconds
});
</script>

</body>
</html>
