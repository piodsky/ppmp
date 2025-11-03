<?php
// PPMP List page - authentication handled by JavaScript
// No Redis dependency needed - using database-based token authentication
$user = ''; // Will be populated by JavaScript
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PPMP List | PPMP System</title>

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
          --bg-primary: #ffffff;
          --bg-secondary: #f8f9fa;
          --bg-accent: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
          --text-primary: #1e3a8a;
          --text-muted: #6b7280;
          --border-light: rgba(0,0,0,0.1);
          --shadow-color: rgba(0,0,0,0.1);

          /* PPMP List specific variables */
          --table-text: var(--text-primary);
          --section-title-bg: var(--bg-accent);
          --form-section-bg: var(--bg-secondary);
          --table-header-bg: var(--bg-accent);
          --btn-primary-bg: var(--bg-accent);
          --btn-secondary-bg: linear-gradient(135deg, #6b7280 0%, #374151 100%);
          --input-bg: rgba(255,255,255,0.9);
          --input-border: rgba(30, 58, 138, 0.2);
          --modal-bg: var(--bg-secondary);
          --text-on-dark: #ffffff;
          --text-muted-dark: var(--text-muted);
          --border-dark: var(--border-light);
          --shadow-dark: var(--shadow-color);
      }

      /* Dark theme overrides - Charcoal Gray + White + Blue */
      [data-theme="dark"] {
          --bg-primary: #374151;
          --bg-secondary: #1f2937;
          --bg-accent: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
          --text-primary: #ffffff;
          --text-muted: #9ca3af;
          --border-light: rgba(255,255,255,0.1);
          --shadow-color: rgba(0,0,0,0.4);

          --input-bg: rgba(255,255,255,0.08);
          --input-border: rgba(59, 130, 246, 0.3);
      }

      .table th, .table td {
      vertical-align: middle;
      color: var(--table-text);
      }

      /* Mobile responsiveness for PPMP List */
      @media (max-width: 1200px) {
          .table th, .table td {
              padding: 8px 4px;
              font-size: 0.85rem;
          }

          .table th:nth-child(1), .table td:nth-child(1) { /* # column */
              width: 50px;
              min-width: 40px;
          }

          .table th:nth-child(2), .table td:nth-child(2) { /* PPMP Number */
              width: 120px;
              min-width: 100px;
          }

          .table th:nth-child(3), .table td:nth-child(3) { /* Plan Year */
              width: 80px;
              min-width: 70px;
          }

          .table th:nth-child(4), .table td:nth-child(4) { /* Status */
              width: 100px;
              min-width: 90px;
          }

          .table th:nth-child(5), .table td:nth-child(5) { /* Department */
              width: 150px;
              min-width: 120px;
          }

          .table th:nth-child(6), .table td:nth-child(6) { /* Total Items */
              width: 80px;
              min-width: 70px;
          }

          .table th:nth-child(7), .table td:nth-child(7) { /* Total Cost */
              width: 100px;
              min-width: 90px;
          }

          .table th:nth-child(8), .table td:nth-child(8) { /* Created By */
              width: 120px;
              min-width: 100px;
          }

          .table th:nth-child(9), .table td:nth-child(9) { /* Date Created */
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

          .d-flex.justify-content-between.align-items-center.mb-4 {
              flex-direction: column;
              gap: 1rem;
              text-align: center;
          }

          .d-flex.align-items-center {
              flex-direction: column;
              gap: 0.5rem;
          }

          .btn-group {
              width: 100%;
              flex-direction: column;
          }

          .btn-group .btn {
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

          /* Hide less important columns on mobile */
          .table th:nth-child(6), .table td:nth-child(6), /* Total Items */
          .table th:nth-child(8), .table td:nth-child(8), /* Created By */
          .table th:nth-child(9), .table td:nth-child(9) { /* Date Created */
              display: none;
          }

          .table th:nth-child(2), .table td:nth-child(2) { /* PPMP Number */
              width: 100px;
              min-width: 80px;
          }

          .table th:nth-child(3), .table td:nth-child(3) { /* Plan Year */
              width: 70px;
              min-width: 60px;
          }

          .table th:nth-child(4), .table td:nth-child(4) { /* Status */
              width: 90px;
              min-width: 80px;
          }

          .table th:nth-child(5), .table td:nth-child(5) { /* Department */
              width: 120px;
              min-width: 100px;
          }

          .table th:nth-child(7), .table td:nth-child(7) { /* Total Cost */
              width: 90px;
              min-width: 80px;
          }

          .table th:nth-child(10), .table td:nth-child(10) { /* Actions */
              width: 80px;
              min-width: 70px;
          }

          .status-badge {
              font-size: 0.7rem;
              padding: 2px 6px;
          }

          .btn {
              font-size: 0.8rem;
              padding: 4px 8px;
          }
      }

      @media (max-width: 480px) {
          main {
              padding: 0.5rem !important;
          }

          .container-fluid {
              padding: 0;
          }

          .card {
              margin: 0.5rem;
              border-radius: 8px;
          }

          .card-header {
              padding: 1rem;
          }

          .card-header h5 {
              font-size: 1.1rem;
              margin-bottom: 0.5rem;
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
          .table th:nth-child(3), .table td:nth-child(3), /* Plan Year */
          .table th:nth-child(5), .table td:nth-child(5) { /* Department */
              display: none;
          }

          .table th:nth-child(2), .table td:nth-child(2) { /* PPMP Number */
              width: 80px;
              min-width: 70px;
          }

          .table th:nth-child(4), .table td:nth-child(4) { /* Status */
              width: 80px;
              min-width: 70px;
          }

          .table th:nth-child(7), .table td:nth-child(7) { /* Total Cost */
              width: 80px;
              min-width: 70px;
          }

          .table th:nth-child(10), .table td:nth-child(10) { /* Actions */
              width: 70px;
              min-width: 60px;
          }

          .status-badge {
              font-size: 0.65rem;
              padding: 1px 4px;
          }

          .btn {
              font-size: 0.75rem;
              padding: 3px 6px;
          }

          .btn i {
              font-size: 0.7rem;
          }
      }
      .section-title {
          background: var(--section-title-bg);
          color: var(--text-on-dark);
          padding: 15px 20px;
          margin-bottom: 20px;
          border-radius: 10px;
          box-shadow: 0 4px 15px var(--shadow-dark);
          border: 1px solid var(--border-dark);
      }
      .form-section {
          border: none;
          padding: 25px;
          margin-bottom: 25px;
          background: var(--form-section-bg);
          border-radius: 15px;
          box-shadow: 0 4px 20px var(--shadow-dark);
          border: 1px solid var(--border-dark);
          color: var(--text-on-dark);
      }
      .table thead th {
          background: var(--table-header-bg);
          color: var(--text-on-dark) !important;
          border: none;
          font-weight: 600;
          border: 1px solid var(--border-dark);
      }
      .table tbody tr {
          background: rgba(255,255,255,0.05);
          border-bottom: 1px solid var(--border-dark);
      }
      .table tbody tr:hover {
          background: rgba(255,255,255,0.1);
      }
      .btn-primary {
          background: var(--btn-primary-bg);
          border: 1px solid rgba(255,255,255,0.2);
          border-radius: 8px;
          padding: 10px 20px;
          font-weight: 600;
          box-shadow: 0 4px 15px rgba(74, 85, 104, 0.3);
          color: white;
      }
      .btn-primary:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(74, 85, 104, 0.4);
      }
      .status-badge {
          padding: 4px 12px;
          border-radius: 20px;
          font-size: 0.8rem;
          font-weight: 600;
      }
      .status-draft {
          background: #e3f2fd;
          color: #1976d2;
      }
      .status-saved {
          background: #e8f5e8;
          color: #2e7d32;
      }
      .status-submitted {
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
  </style>
</head>
<body class="g-sidenav-show" data-theme="light" style="background: var(--bg-primary); min-height: 100vh; color: var(--text-primary); transition: background-color 0.3s ease, color 0.3s ease;">

  <?php include 'sidebar.php'; ?>

  <!-- Main content -->
  <main class="main-content position-relative border-radius-lg ps ps--active-y" style="margin-left: 280px; padding: 1.5rem;">
    <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <img src="assets/logo.svg" alt="PPMP Logo" style="width: 50px; height: 50px; margin-right: 15px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
            <div>
                <h3 class="text-primary mb-0">PPMP Documents</h3>
                <small class="text-muted">View and manage all created PPMP documents</small>
            </div>
        </div>
        <button class="btn btn-outline-secondary" id="themeToggle" title="Toggle Theme">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>
    </div>

      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">📋 PPMP List</h5>
          <div>
            <button class="btn btn-success me-2" onclick="window.location.href='ppmp.php'">
              <i class="fas fa-plus"></i> Create New PPMP
            </button>
            <button class="btn btn-info" onclick="loadPPMPList()">
              <i class="fas fa-sync"></i> Refresh
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="ppmpTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>PPMP Number</th>
                  <th>Plan Year</th>
                  <th>Status</th>
                  <th>Department</th>
                  <th>Total Items</th>
                  <th>Total Cost</th>
                  <th>Created By</th>
                  <th>Date Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="ppmpTableBody">
                <!-- PPMP list will be loaded here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Argon Core JS -->
  <script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
  <script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

  <!-- Your JS -->
  <script src="../apiPPMP/api_config.js.php"></script>
  <script src="js/ppmp_list.js"></script>

  <script>
    // User data will be populated by token manager above
    let userRole = 'user'; // Default, will be updated by authentication check
    let currentUser = {
        username: '',
        role: 'user'
    };

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

async function verifyToken() {
    const token = localStorage.getItem('access_token');
    if (!token) return false;

    try {
        const response = await fetch(`${API_BASE_URL}/api_verify_token.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.ok) {
            const data = await response.json();
            return data.status === 'success';
        }
        return false;
    } catch (error) {
        console.error('Token verification error:', error);
        return false;
    }
}

  </script>
  <script src="js/ppmp_list.js"></script>

  <script>
  // Theme Management for PPMP List Page
  class PPMPListThemeManager {
      constructor() {
          this.currentTheme = this.getInitialTheme();
          this.init();
      }

      getInitialTheme() {
          // Check for saved theme first
          const savedTheme = localStorage.getItem('ppmp-theme');
          if (savedTheme) {
              return savedTheme;
          }

          // Auto-detect system preference
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

          // Update body background and text color
          if (theme === 'dark') {
              document.body.style.background = 'linear-gradient(135deg, #374151 0%, #1f2937 100%)';
              document.body.style.color = '#ffffff';
          } else {
              document.body.style.background = '#ffffff';
              document.body.style.color = '#1e3a8a';
          }

          // Force update of CSS custom properties
          const root = document.documentElement;
          if (theme === 'dark') {
              root.style.setProperty('--text-primary', '#ffffff');
              root.style.setProperty('--text-muted', '#9ca3af');
              root.style.setProperty('--input-bg', 'rgba(255,255,255,0.08)');
              root.style.setProperty('--input-border', 'rgba(59, 130, 246, 0.3)');
          } else {
              root.style.setProperty('--text-primary', '#1e3a8a');
              root.style.setProperty('--text-muted', '#6b7280');
              root.style.setProperty('--input-bg', 'rgba(255,255,255,0.9)');
              root.style.setProperty('--input-border', 'rgba(30, 58, 138, 0.2)');
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

  // Initialize theme manager when DOM loads
  document.addEventListener('DOMContentLoaded', function() {
      new PPMPListThemeManager();
  });
  </script>
</body>
</html>
