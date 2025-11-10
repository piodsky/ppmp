<?php
// Consolidated Items page - PHP-based authentication like dashboard
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Consolidated PPMP Items | PPMP System</title>

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

          /* PPMP specific variables */
          --section-title-bg: var(--bg-accent);
          --form-section-bg: var(--bg-secondary);
          --table-header-bg: var(--bg-accent);
          --input-bg: rgba(255,255,255,0.9);
          --input-border: rgba(30, 58, 138, 0.2);
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
        color: var(--text-primary);
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
          background: var(--bg-accent);
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
      .consolidated-badge {
          background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
          color: white;
          padding: 4px 12px;
          border-radius: 20px;
          font-size: 0.8rem;
          font-weight: 600;
      }

      /* Center text for monthly columns (Jan-Dec) and department columns */
      #appReportTable td:nth-child(n+4):nth-child(-n+15),
      #deptReportTable td:nth-child(n+4):nth-child(-n+13) {
          text-align: center;
      }

      #appReportTable th:nth-child(n+4):nth-child(-n+15),
      #deptReportTable th:nth-child(n+4):nth-child(-n+13) {
          text-align: center;
      }

      /* Make report tables more compact for one-page view */
      #appReportTable,
      #deptReportTable {
          font-size: 0.85rem;
      }

      #appReportTable th,
      #deptReportTable th {
          font-size: 0.8rem;
          padding: 8px 4px;
          font-weight: 600;
      }

      #appReportTable td,
      #deptReportTable td {
          padding: 6px 4px;
          font-size: 0.8rem;
      }

      /* Add scrollable container for report tables */
      .report-table-container {
          max-height: 600px;
          overflow-y: auto;
          border: 1px solid var(--border-light);
          border-radius: 8px;
          margin-top: 10px;
      }

      .report-table-container table {
          margin-bottom: 0;
      }

      .report-table-container thead th {
          position: sticky;
          top: 0;
          background: var(--table-header-bg);
          color: var(--text-on-dark);
          z-index: 10;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                <h3 class="text-primary mb-0">Consolidated PPMP Items</h3>
                <small class="text-muted">Aggregated items from all approved PPMP documents</small>
            </div>
        </div>
        <button class="btn btn-outline-secondary" id="themeToggle" title="Toggle Theme">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>
    </div>

      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">📊 Consolidated Items</h5>
          <div class="d-flex justify-content-end flex-wrap gap-2">
            <div class="dropdown btn-group">
              <button class="btn btn-primary" onclick="previewAPPReport()">
                <i class="fas fa-calendar-alt"></i> APP Report
              </button>
              <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="#" onclick="downloadAPPReport()">
                  <i class="fas fa-download"></i> Download PDF
                </a>
              </div>
            </div>
            <button class="btn btn-success" onclick="exportConsolidated()">
              <i class="fas fa-download"></i> Export
            </button>
            <div class="dropdown">
              <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-building"></i> Dept Report <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="previewDepartmentReport()"><i class="fas fa-eye"></i> Preview PDF</a></li>
                <li><a class="dropdown-item" href="#" onclick="downloadDepartmentReport()"><i class="fas fa-download"></i> Download PDF</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportDepartmentReport()"><i class="fas fa-file-csv"></i> Export CSV</a></li>
              </ul>
            </div>
            <button class="btn btn-info" onclick="loadConsolidatedItems()">
              <i class="fas fa-sync"></i> Refresh
            </button>
            <div class="dropdown btn-group">
              <button class="btn btn-danger" onclick="previewPDF()">
                <i class="fas fa-eye"></i> Preview PDF
              </button>
              <button class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="#" onclick="exportToPDF()">
                  <i class="fas fa-download"></i> Download PDF
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <input type="text" id="searchInput" class="form-control" placeholder="Search by item name or description..." style="max-width: 400px;">
            </div>
            <div class="col-md-6 d-flex justify-content-end">
              <select id="yearFilter" class="form-control" style="max-width: 200px;" onchange="loadConsolidatedItems()">
                <option value="">All Years</option>
                <!-- Years will be loaded dynamically -->
              </select>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped" id="consolidatedTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Item Code</th>
                  <th>Item Name</th>
                  <th>Description</th>
                  <th>Unit</th>
                  <th>Unit Cost</th>
                  <th>Total Qty</th>
                  <th>Total Cost</th>
                  <th>PPMP Count</th>
                </tr>
              </thead>
              <tbody id="consolidatedTableBody">
                <!-- Consolidated items will be loaded here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>


      <!-- Summary Section -->
      <div class="row mt-4">
        <div class="col-md-4">
          <div class="form-section">
            <h6 class="section-title">📈 Summary</h6>
            <div class="mb-2">
              <strong>Total Items:</strong> <span id="totalItems">0</span>
            </div>
            <div class="mb-2">
              <strong>Total Cost:</strong> ₱<span id="totalCost">0.00</span>
            </div>
            <div class="mb-2">
              <strong>Approved PPMPs:</strong> <span id="approvedPPMPs">0</span>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="form-section">
            <h6 class="section-title">📋 Approved PPMP Documents</h6>
            <div id="approvedPPMPList">
              <!-- List of approved PPMPs will be loaded here -->
            </div>
          </div>
        </div>
      </div>

      <!-- APP Report Section -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="form-section">
            <h6 class="section-title">📄 Annual Procurement Plan (APP) Report</h6>
            <div class="report-table-container">
              <table class="table table-striped" id="appReportTable">
                <thead>
                  <tr>
                    <th>Item Code</th>
                    <th>Item Name & Specifications</th>
                    <th>Unit</th>
                    <th>Jan</th>
                    <th>Feb</th>
                    <th>Mar</th>
                    <th>Apr</th>
                    <th>May</th>
                    <th>Jun</th>
                    <th>Jul</th>
                    <th>Aug</th>
                    <th>Sep</th>
                    <th>Oct</th>
                    <th>Nov</th>
                    <th>Dec</th>
                    <th>Total Qty</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                  </tr>
                </thead>
                <tbody id="appReportTableBody">
                  <tr>
                    <td colspan="18" class="text-center">
                      <i class="fas fa-spinner fa-spin"></i> Loading APP report data...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Department Report Section -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="form-section">
            <h6 class="section-title">🏢 Department Consolidated Report</h6>
            <div class="report-table-container">
              <table class="table table-striped" id="deptReportTable">
                <thead id="deptReportTableHead">
                  <tr>
                    <th>Item Code</th>
                    <th>Item Name & Specifications</th>
                    <th>Unit</th>
                    <th colspan="10">Departments</th>
                    <th>Total Qty</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                  </tr>
                </thead>
                <tbody id="deptReportTableBody">
                  <tr>
                    <td colspan="16" class="text-center">
                      <i class="fas fa-spinner fa-spin"></i> Loading department report data...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
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
  <script src="js/consolidated_items.js"></script>

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


  // Theme Management for Consolidated Items Page
  class ConsolidatedItemsThemeManager {
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

  // Preview PDF function
  function previewPDF() {
      const token = getAccessToken();
      window.open('generate_consolidated_report.php?preview=1&token=' + encodeURIComponent(token), '_blank');
  }

  // Export to PDF function (download)
  function exportToPDF() {
      if (confirm('Download PDF report for consolidated items?')) {
          const token = getAccessToken();
          window.location.href = 'generate_consolidated_report.php?token=' + encodeURIComponent(token);
      }
  }

  // Preview Department Report function
  function previewDepartmentReport() {
      const token = getAccessToken();
      window.open('generate_department_report.php?preview=1&token=' + encodeURIComponent(token), '_blank');
  }

  // Download Department Report function
  function downloadDepartmentReport() {
      if (confirm('Download PDF report showing items by department?')) {
          const token = getAccessToken();
          window.location.href = 'generate_department_report.php?token=' + encodeURIComponent(token);
      }
  }

  // Load APP Report Data
  function loadAPPReportData() {
      authenticatedFetch(`${API_BASE_URL}/api_app_report_data.php`)
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  displayAPPReportData(data.data);
              } else {
                  document.getElementById('appReportTableBody').innerHTML = `
                      <tr>
                          <td colspan="18" class="text-center text-danger">
                              <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Failed to load APP report data'}
                          </td>
                      </tr>
                  `;
              }
          })
          .catch(error => {
              console.error('Error loading APP report data:', error);
              document.getElementById('appReportTableBody').innerHTML = `
                  <tr>
                      <td colspan="18" class="text-center text-danger">
                          <i class="fas fa-exclamation-triangle"></i> Error loading APP report data
                      </td>
                  </tr>
              `;
          });
  }

  // Display APP Report Data
  function displayAPPReportData(data) {
      const tbody = document.getElementById('appReportTableBody');
      tbody.innerHTML = '';

      data.forEach(item => {
          if (item.type === 'category') {
              // Category header row
              tbody.innerHTML += `
                  <tr class="table-info">
                      <td colspan="18" class="text-left font-weight-bold">
                          ${item.name}
                      </td>
                  </tr>
              `;
          } else {
              // Item data row
              tbody.innerHTML += `
                  <tr>
                      <td>${item.item_code}</td>
                      <td>${item.item_name}</td>
                      <td>${item.unit}</td>
                      <td>${item.jan_qty}</td>
                      <td>${item.feb_qty}</td>
                      <td>${item.mar_qty}</td>
                      <td>${item.apr_qty}</td>
                      <td>${item.may_qty}</td>
                      <td>${item.jun_qty}</td>
                      <td>${item.jul_qty}</td>
                      <td>${item.aug_qty}</td>
                      <td>${item.sep_qty}</td>
                      <td>${item.oct_qty}</td>
                      <td>${item.nov_qty}</td>
                      <td>${item.dec_qty}</td>
                      <td>${item.total_quantity}</td>
                      <td>${item.unit_cost}</td>
                      <td>${item.total_cost}</td>
                  </tr>
              `;
          }
      });
  }

  // Load Department Report Data
  function loadDepartmentReportData() {
      authenticatedFetch(`${API_BASE_URL}/api_department_report_data.php`)
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  displayDepartmentReportData(data.departments, data.data);
              } else {
                  document.getElementById('deptReportTableBody').innerHTML = `
                      <tr>
                          <td colspan="16" class="text-center text-danger">
                              <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Failed to load department report data'}
                          </td>
                      </tr>
                  `;
              }
          })
          .catch(error => {
              console.error('Error loading department report data:', error);
              document.getElementById('deptReportTableBody').innerHTML = `
                  <tr>
                      <td colspan="16" class="text-center text-danger">
                          <i class="fas fa-exclamation-triangle"></i> Error loading department report data
                      </td>
                  </tr>
              `;
          });
  }

  // Display Department Report Data
  function displayDepartmentReportData(departments, data) {
      // Update table header with department names
      const thead = document.getElementById('deptReportTableHead');
      let headerHTML = `
          <tr>
              <th>Item Code</th>
              <th>Item Name & Specifications</th>
              <th>Unit</th>
      `;

      departments.forEach(dept => {
          headerHTML += `<th>${dept}</th>`;
      });

      headerHTML += `
              <th>Total Qty</th>
              <th>Unit Cost</th>
              <th>Total Cost</th>
          </tr>
      `;
      thead.innerHTML = headerHTML;

      // Update table body
      const tbody = document.getElementById('deptReportTableBody');
      tbody.innerHTML = '';

      data.forEach(item => {
          let rowHTML = `
              <tr>
                  <td>${item.item_code}</td>
                  <td>${item.item_name}</td>
                  <td>${item.unit}</td>
          `;

          item.departments.forEach(qty => {
              rowHTML += `<td>${qty}</td>`;
          });

          rowHTML += `
                  <td>${item.total_quantity}</td>
                  <td>${item.unit_cost}</td>
                  <td>${item.total_cost}</td>
              </tr>
          `;
          tbody.innerHTML += rowHTML;
      });
  }

  // Load consolidated items function
  function loadConsolidatedItems() {
      const searchTerm = document.getElementById('searchInput').value;
      const yearFilter = document.getElementById('yearFilter').value;

      // Show loading
      const tableBody = document.getElementById('consolidatedTableBody');
      tableBody.innerHTML = `
          <tr>
              <td colspan="9" class="text-center py-4">
                  <i class="fas fa-spinner fa-spin fa-2x"></i>
                  <p class="mt-2">Loading consolidated items...</p>
              </td>
          </tr>
      `;

      authenticatedFetch(`${API_BASE_URL}/api_get_consolidated_items.php?search=${encodeURIComponent(searchTerm)}&year=${encodeURIComponent(yearFilter)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Consolidated items API response:', data); // Debug log
            if (data.success) {
                console.log('Items data:', data.consolidated_items); // Debug log
                displayConsolidatedItems(data.consolidated_items);
                updateSummary(data.summary);
                loadApprovedPPMPList(data.approved_ppmp_list);
            } else {
                showError('Failed to load consolidated items', 'consolidatedTableBody', 9);
            }
        })
          .catch(error => {
              console.error('Error loading consolidated items:', error);
              showError('Network error loading consolidated items', 'consolidatedTableBody', 9);
          });
  }

  // Display consolidated items
  function displayConsolidatedItems(items) {
      const tableBody = document.getElementById('consolidatedTableBody');

      if (!items || items.length === 0) {
          tableBody.innerHTML = `
              <tr>
                  <td colspan="9" class="text-center py-4">
                      <i class="fas fa-database fa-3x text-muted mb-3"></i>
                      <h5 class="text-muted">No consolidated items found</h5>
                      <p class="text-muted">No items match your search criteria.</p>
                  </td>
              </tr>
          `;
          return;
      }

      tableBody.innerHTML = '';

      items.forEach((item, index) => {
          tableBody.innerHTML += `
              <tr>
                  <td>${index + 1}</td>
                  <td><span class="fw-bold">${item.item_code}</span></td>
                  <td>${item.item_name}</td>
                  <td>${item.description}</td>
                  <td>${item.unit}</td>
                  <td><span class="fw-bold text-success">₱${parseFloat(item.unit_cost).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span></td>
                  <td>${item.total_quantity}</td>
                  <td><span class="fw-bold text-primary">₱${parseFloat(item.total_cost).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span></td>
                  <td><span class="consolidated-badge">${item.ppmp_count}</span></td>
              </tr>
          `;
      });
  }

  // Update summary section
  function updateSummary(summary) {
      document.getElementById('totalItems').textContent = summary.total_items;
      document.getElementById('totalCost').textContent = parseFloat(summary.total_cost).toLocaleString('en-US', { minimumFractionDigits: 2 });
      document.getElementById('approvedPPMPs').textContent = summary.approved_ppmp_count;
  }

  // Load approved PPMP list
  function loadApprovedPPMPList(ppmpList) {
      const container = document.getElementById('approvedPPMPList');
      container.innerHTML = '';

      if (ppmpList.length === 0) {
          container.innerHTML = '<p class="text-muted">No approved PPMP documents found.</p>';
          return;
      }

      ppmpList.forEach(ppmp => {
          container.innerHTML += `
              <div class="mb-2">
                  <strong>${ppmp.year} - ${ppmp.department}</strong>
                  <small class="text-muted d-block">Approved: ${new Date(ppmp.approved_at).toLocaleDateString()}</small>
              </div>
          `;
      });
  }

  // Export consolidated items
  function exportConsolidated() {
      if (confirm('Export consolidated items to CSV?')) {
          const token = getAccessToken();
          window.location.href = 'generate_consolidated_report.php?export=csv&token=' + encodeURIComponent(token);
      }
  }

  // Export department report
  function exportDepartmentReport() {
      if (confirm('Export department report to CSV?')) {
          const token = getAccessToken();
          window.location.href = 'generate_department_report.php?export=csv&token=' + encodeURIComponent(token);
      }
  }

  // Preview APP Report
  function previewAPPReport() {
      const token = getAccessToken();
      window.open('generate_app_report.php?preview=1&token=' + encodeURIComponent(token), '_blank');
  }

  // Download APP Report
  function downloadAPPReport() {
      if (confirm('Download APP report PDF?')) {
          const token = getAccessToken();
          window.location.href = 'generate_app_report.php?token=' + encodeURIComponent(token);
      }
  }

  // Load available years for filter
  function loadAvailableYears() {
      authenticatedFetch(`${API_BASE_URL}/api_get_available_years.php`)
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  const yearFilter = document.getElementById('yearFilter');
                  yearFilter.innerHTML = '<option value="">All Years</option>';

                  data.years.forEach(year => {
                      yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                  });
              }
          })
          .catch(error => {
              console.error('Error loading available years:', error);
          });
  }

  // Show error function
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

  // Initialize theme manager when DOM loads
  document.addEventListener('DOMContentLoaded', function() {
      new ConsolidatedItemsThemeManager();
      loadAvailableYears();
      loadConsolidatedItems();
      loadAPPReportData();
      loadDepartmentReportData();

      // Add search functionality
      document.getElementById('searchInput').addEventListener('input', function() {
          clearTimeout(this.searchTimeout);
          this.searchTimeout = setTimeout(loadConsolidatedItems, 500);
      });
  });
  </script>
</body>
</html>
