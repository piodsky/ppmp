<?php
// Manage PPMP Items page - API-based authentication
require_once __DIR__ . '/../vendor/autoload.php';
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

// Validate token via API call
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PPMP Items Management | PPMP System</title>

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
          --table-text: var(--text-primary);
          --section-title-bg: var(--bg-accent);
          --form-section-bg: var(--bg-secondary);
          --table-header-bg: var(--bg-accent);
          --btn-primary-bg: var(--bg-accent);
          --btn-secondary-bg: linear-gradient(135deg, #718096 0%, #4a5568 100%);
          --input-bg: rgba(255,255,255,0.1);
          --input-border: rgba(255,255,255,0.2);
          --modal-bg: var(--bg-secondary);
          --text-on-dark: var(--text-primary);
          --text-muted-dark: var(--text-muted);
          --border-dark: var(--border-light);
          --shadow-dark: var(--shadow-color);
      }

      .table th, .table td {
        vertical-align: middle;
        color: var(--table-text);
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
          color: var(--text-on-dark);
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
      .btn-secondary {
          background: var(--btn-secondary-bg);
          border: 1px solid rgba(255,255,255,0.2);
          color: white;
      }
      .form-control {
          border-radius: 8px;
          border: 1px solid var(--input-border);
          background: var(--input-bg);
          color: var(--text-on-dark);
          transition: all 0.3s ease;
      }
      .form-control:focus {
          border-color: #63b3ed;
          box-shadow: 0 0 0 0.2rem rgba(99, 179, 237, 0.25);
          background: rgba(255,255,255,0.15);
      }

      .form-control option {
          background: var(--bg-secondary);
          color: var(--text-primary);
      }

      .form-control option:hover,
      .form-control option:focus {
          background: rgba(99, 179, 237, 0.2);
      }

      /* Enhanced validation styles */
      .form-control:invalid {
          border-color: #e53e3e;
          background: rgba(229, 62, 62, 0.1);
      }

      .form-control:valid {
          border-color: #38a169;
          background: rgba(56, 161, 105, 0.1);
      }

      .was-validated .form-control:invalid {
          border-color: #e53e3e;
          background: rgba(229, 62, 62, 0.1);
      }

      .was-validated .form-control:valid {
          border-color: #38a169;
          background: rgba(56, 161, 105, 0.1);
      }

      .invalid-feedback {
          display: none;
          color: #e53e3e;
          font-size: 0.875rem;
          margin-top: 0.25rem;
      }

      .was-validated .form-control:invalid ~ .invalid-feedback {
          display: block;
      }

      .text-danger {
          color: #e53e3e !important;
      }

      /* Loading state for submit button */
      .btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
      }

      /* Pagination styles */
      .pagination .page-link {
          color: var(--text-primary);
          background: rgba(255,255,255,0.1);
          border: 1px solid rgba(255,255,255,0.2);
          transition: all 0.3s ease;
      }

      .pagination .page-link:hover {
          background: rgba(255,255,255,0.2);
          border-color: rgba(255,255,255,0.3);
      }

      .pagination .page-item.active .page-link {
          background: var(--bg-accent);
          border-color: rgba(255,255,255,0.3);
      }

      .pagination .page-item.disabled .page-link {
          background: rgba(255,255,255,0.05);
          color: var(--text-muted-dark);
      }

      /* Search and filter styles */
      #searchInput, #categoryFilter {
          background: rgba(255,255,255,0.1);
          border: 1px solid rgba(255,255,255,0.2);
          color: var(--text-primary);
      }

      #searchInput:focus, #categoryFilter:focus {
          background: rgba(255,255,255,0.15);
          border-color: #63b3ed;
          box-shadow: 0 0 0 0.2rem rgba(99, 179, 237, 0.25);
      }

      #searchInput::placeholder {
          color: var(--text-muted-dark);
      }

      /* Badge styles */
      .badge {
          background: var(--bg-accent);
          color: white;
      }
      .form-control::placeholder {
          color: var(--text-muted-dark);
      }
      .modal-content {
          background: var(--modal-bg);
          color: var(--text-on-dark);
          border: 1px solid var(--border-dark);
      }
      .modal-header {
          border-bottom: 1px solid var(--border-dark);
      }
      .modal-footer {
          border-top: 1px solid var(--border-dark);
      }
      .text-muted {
          color: var(--text-muted-dark) !important;
      }
      .table th:nth-child(4), .table td:nth-child(4) {
          width: 250px;
          min-width: 250px;
          max-width: 250px;
          word-wrap: break-word;
          white-space: normal;
          max-height: 100px;
          overflow: auto;
      }
  </style>
</head>
<body class="g-sidenav-show" style="background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%); min-height: 100vh; color: white;">

  <?php include 'sidebar.php'; ?>

  <!-- Main content -->
  <main class="main-content position-relative border-radius-lg ps ps--active-y" style="margin-left: 280px; padding: 1.5rem;">
    <div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <img src="assets/logo.svg" alt="PPMP Logo" style="width: 50px; height: 50px; margin-right: 15px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
        <div>
            <h3 class="text-primary mb-0">PPMP Items Management</h3>
            <small class="text-muted">Manage and organize procurement items</small>
        </div>
    </div>
      <!-- Search and Filter Section -->
      <div class="card shadow mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <input type="text" class="form-control" id="searchInput" placeholder="🔍 Search items...">
            </div>
            <div class="col-md-3">
              <select class="form-control" id="categoryFilter">
                <option value="">All Categories</option>
                <option value="Office Supplies">Office Supplies</option>
                <option value="IT Equipment">IT Equipment</option>
                <option value="Furniture">Furniture</option>
                <option value="Cleaning Supplies">Cleaning Supplies</option>
                <option value="Electrical">Electrical</option>
                <option value="Plumbing">Plumbing</option>
                <option value="Tools & Hardware">Tools & Hardware</option>
                <option value="Safety Equipment">Safety Equipment</option>
                <option value="Medical Supplies">Medical Supplies</option>
                <option value="Educational Materials">Educational Materials</option>
                <option value="Food & Beverages">Food & Beverages</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Transportation">Transportation</option>
                <option value="Miscellaneous">Miscellaneous</option>
              </select>
            </div>
            <div class="col-md-2">
              <button class="btn btn-outline-secondary w-100" id="clearFilters">🧹 Clear</button>
            </div>
            <div class="col-md-3 text-end">
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">➕ Add Item</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">📦 PPMP Items Management <span class="badge bg-primary" id="totalItems">0</span></h5>
          <div class="d-flex align-items-center gap-2">
            <small class="text-muted">Items per page:</small>
            <select class="form-control form-control-sm" id="itemsPerPage" style="width: auto;">
              <option value="25">25</option>
              <option value="50" selected>50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="itemsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Items Description</th>
                <th>Unit</th>
                <th>Unit Cost</th>
                <th>Category</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- JS dynamically fills this -->
            </tbody>
            </table>
          </div>

          <!-- Pagination Controls -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" id="paginationInfo">No items found</div>
            <nav aria-label="Items pagination">
              <ul class="pagination pagination-sm mb-0" id="paginationControls">
                <!-- Pagination buttons will be generated here -->
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal: Add Item -->
  <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="itemForm">
          <div class="modal-header">
            <h5 class="modal-title" id="addItemModalLabel">➕ Add Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="itemCode" class="form-label" style="color: var(--text-primary);">Item Code: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="itemCode" name="item_code" placeholder="e.g., PEN-BLK-001" list="itemCodeSuggestions" required>
                <datalist id="itemCodeSuggestions"></datalist>
                <div class="invalid-feedback">Item Code is required.</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="itemName" class="form-label" style="color: var(--text-primary);">Item Name: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="itemName" name="item_name" placeholder="Short name" required>
                <div class="invalid-feedback">Item Name is required.</div>
              </div>
            </div>
            <div class="mb-3">
              <label for="itemDescription" class="form-label" style="color: var(--text-primary);">Item Description: <span class="text-danger">*</span></label>
              <textarea class="form-control" id="itemDescription" name="item_description" rows="2" placeholder="Detailed description" required></textarea>
              <div class="invalid-feedback">Item Description is required.</div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="unit" class="form-label" style="color: var(--text-primary);">Unit: <span class="text-danger">*</span></label>
                <select class="form-control" id="unit" name="unit" required>
                  <option value="">Select Unit</option>
                  <option value="pcs">Pieces (pcs)</option>
                  <option value="kg">Kilograms (kg)</option>
                  <option value="liters">Liters (L)</option>
                  <option value="meters">Meters (m)</option>
                  <option value="boxes">Boxes</option>
                  <option value="packs">Packs</option>
                  <option value="sets">Sets</option>
                  <option value="rolls">Rolls</option>
                  <option value="tubes">Tubes</option>
                  <option value="bottles">Bottles</option>
                  <option value="cans">Cans</option>
                  <option value="reams">Reams</option>
                  <option value="pads">Pads</option>
                  <option value="books">Books</option>
                </select>
                <div class="invalid-feedback">Please select a unit.</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="unitCost" class="form-label" style="color: var(--text-primary);">Unit Cost: <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="unitCost" name="unit_cost" step="0.01" min="0.01" placeholder="0.00" required>
                <div class="invalid-feedback">Unit Cost must be greater than 0.</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="category" class="form-label" style="color: var(--text-primary);">Category: <span class="text-danger">*</span></label>
                <select class="form-control" id="category" name="category" required>
                  <option value="">Select Category</option>
                  <option value="Office Supplies">Office Supplies</option>
                  <option value="IT Equipment">IT Equipment</option>
                  <option value="Furniture">Furniture</option>
                  <option value="Cleaning Supplies">Cleaning Supplies</option>
                  <option value="Electrical">Electrical</option>
                  <option value="Plumbing">Plumbing</option>
                  <option value="Tools & Hardware">Tools & Hardware</option>
                  <option value="Safety Equipment">Safety Equipment</option>
                  <option value="Medical Supplies">Medical Supplies</option>
                  <option value="Educational Materials">Educational Materials</option>
                  <option value="Food & Beverages">Food & Beverages</option>
                  <option value="Maintenance">Maintenance</option>
                  <option value="Transportation">Transportation</option>
                  <option value="Miscellaneous">Miscellaneous</option>
                </select>
                <div class="invalid-feedback">Please select a category.</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Cancel</button>
            <button type="submit" class="btn btn-primary">💾 Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Argon Core JS -->
  <script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
  <script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

  <!-- Your JS -->
  <script src="../api_config.js.php"></script>
  <script src="js/manage_ppmp_items.js"></script>
</body>
</html>
