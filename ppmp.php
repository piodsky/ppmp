<?php
// PPMP page - API-based authentication
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

// Set user data for form population
$user_department = $department;
$user_contact = $firstname . ' ' . ($middlename ?? '') . ' ' . $lastname . ' ' . ($name_ext ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Procurement Management Plan</title>

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
            /* Light theme variables - White + Gray + Navy */
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

        .section-title {
            background: var(--section-title-bg);
            color: var(--text-on-dark) !important;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px var(--shadow-dark);
            border: 1px solid var(--border-dark);
        }

        .section-title h6 {
            color: var(--text-on-dark) !important;
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
        /* Compact table styling */
        .table {
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 6px 4px;
            color: var(--text-primary);
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.2;
        }

        .table thead th {
            background: var(--table-header-bg);
            color: var(--text-on-dark);
            border: none;
            font-weight: 600;
            border: 1px solid var(--border-dark);
            font-size: 0.8rem;
            padding: 8px 4px;
        }

        .table tbody tr {
            background: rgba(255,255,255,0.05);
            border-bottom: 1px solid var(--border-dark);
        }

        .table tbody tr:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Compact column widths */
        .table th:nth-child(1), .table td:nth-child(1) { /* Item column */
            width: 200px;
            min-width: 180px;
        }

        .table th:nth-child(2), .table td:nth-child(2) { /* Description column */
            width: 250px;
            min-width: 200px;
        }

        .table th:nth-child(3), .table td:nth-child(3) { /* Unit column */
            width: 60px;
            min-width: 50px;
        }

        /* Month columns - very compact */
        .table th:nth-child(n+4):nth-child(-n+15), .table td:nth-child(n+4):nth-child(-n+15) {
            width: 45px;
            min-width: 40px;
            padding: 4px 2px;
        }

        /* Quarter total columns */
        .table th:nth-child(n+16):nth-child(-n+19), .table td:nth-child(n+16):nth-child(-n+19) {
            width: 55px;
            min-width: 50px;
        }

        /* Cost and total columns */
        .table th:nth-child(n+20), .table td:nth-child(n+20) {
            width: 80px;
            min-width: 70px;
        }

        /* Actions column */
        .table th:last-child, .table td:last-child {
            width: 80px;
            min-width: 70px;
        }

        /* Compact input styling */
        input[type="number"] {
            width: 100%;
            max-width: 40px;
            text-align: center;
            border-radius: 4px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-primary);
            font-size: 0.8rem;
            padding: 2px;
        }

        input[type="number"]:focus {
            background: rgba(255,255,255,0.15);
            border-color: #63b3ed;
        }

        /* Compact select styling */
        .item-select {
            font-size: 0.8rem;
            padding: 8px 12px;
            height: 50px;
            max-width: 360px;
        }

        /* Compact button styling */
        .btn-success {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(56, 161, 105, 0.3);
            color: white !important;
            font-size: 0.85rem;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            color: white !important;
            font-size: 0.8rem;
            padding: 4px 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
            border: 1px solid rgba(255,255,255,0.2);
            color: white !important;
            font-size: 0.8rem;
        }

        .btn-warning {
            background: linear-gradient(135deg, #d69e2e 0%, #b7791f 100%);
            border: 1px solid rgba(255,255,255,0.2);
            color: white !important;
            font-size: 0.8rem;
        }

        .btn-info {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            border: 1px solid rgba(255,255,255,0.2);
            color: white !important;
            font-size: 0.8rem;
        }

        .btn-outline-secondary {
            color: var(--text-primary) !important;
            border-color: var(--border-light) !important;
            background: transparent !important;
        }

        .btn-outline-secondary:hover {
            background: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
        }

        /* Compact form controls */
        .form-control {
            border-radius: 6px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-primary);
            transition: all 0.3s ease;
            font-size: 0.85rem;
            padding: 4px 8px;
        }

        .form-control:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 0.15rem rgba(99, 179, 237, 0.25);
            background: rgba(255,255,255,0.15);
        }

        .form-control::placeholder {
            color: var(--text-muted-dark);
        }

        /* Responsive table container */
        .table-responsive {
            max-height: 60vh;
            overflow-y: auto;
            overflow-x: auto;
        }

        /* Allow table to grow vertically */
        .table-responsive table {
            min-width: 1200px;
        }

        /* Mobile responsiveness */
        @media (max-width: 1200px) {
            .table-responsive {
                font-size: 0.75rem;
            }

            .table th, .table td {
                padding: 4px 2px;
            }

            .table th:nth-child(2), .table td:nth-child(2) {
                width: 180px;
                min-width: 150px;
            }

            .table th:nth-child(n+4):nth-child(-n+15), .table td:nth-child(n+4):nth-child(-n+15) {
                width: 35px;
                min-width: 30px;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                max-height: 50vh;
            }

            .table {
                font-size: 0.7rem;
            }

            .table th, .table td {
                padding: 2px 1px;
            }

            /* Hide some month columns on very small screens */
            .table th:nth-child(n+5):nth-child(-n+6),
            .table td:nth-child(n+5):nth-child(-n+6),
            .table th:nth-child(n+8):nth-child(-n+9),
            .table td:nth-child(n+8):nth-child(-n+9),
            .table th:nth-child(n+11):nth-child(-n+12),
            .table td:nth-child(n+11):nth-child(-n+12),
            .table th:nth-child(n+14):nth-child(-n+15),
            .table td:nth-child(n+14):nth-child(-n+15) {
                display: none;
            }

            /* Adjust remaining columns */
            .table th:nth-child(2), .table td:nth-child(2) {
                width: 150px;
                min-width: 120px;
            }
        }

        /* Dynamic row height adjustment */
        .table td {
            max-height: none;
            height: auto;
            min-height: 40px;
            vertical-align: top;
        }

        .table tbody tr {
            height: auto;
        }

        /* Ensure inputs don't overflow */
        .table td input,
        .table td select {
            max-width: 100%;
            box-sizing: border-box;
            display: block;
        }

        /* Better text wrapping for inputs */
        .table td input[type="text"] {
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
            height: auto;
            min-height: 30px;
            resize: none;
        }

        /* Styles for textarea in table cells */
        .table td textarea {
            word-wrap: break-word;
            white-space: pre-wrap;
            overflow-wrap: break-word;
            height: auto;
            min-height: 30px;
            resize: none;
            overflow: hidden;
            border: none;
            background: transparent;
            color: var(--text-primary);
            font-size: 0.85rem;
            padding: 4px 8px;
            line-height: 1.2;
            width: 100%;
            box-sizing: border-box;
        }

        /* Allow select dropdown to show full options */
        .table td select {
            position: relative;
            z-index: 5;
            color: var(--text-primary) !important;
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
        }

        /* Ensure all select elements are visible */
        select {
            color: var(--text-primary) !important;
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
        }

        /* Ensure select options are visible */
        .table td select option {
            color: #1a202c !important;
            background: #ffffff !important;
        }

        /* Dark theme select options - ensure high contrast */
        [data-theme="dark"] .table td select option {
            color: #ffffff !important;
            background: #2d3748 !important;
        }

        /* Also apply to all select elements, not just table cells */
        select option {
            color: #1a202c !important;
            background: #ffffff !important;
        }

        [data-theme="dark"] select option {
            color: #ffffff !important;
            background: #2d3748 !important;
        }

        /* Extra specific rule for select options in dark theme */
        [data-theme="dark"] .table td select option,
        [data-theme="dark"] select option {
            color: #ffffff !important;
            background-color: #2d3748 !important;
        }

        /* Ensure option hover states are also visible */
        [data-theme="dark"] select option:hover,
        [data-theme="dark"] select option:focus {
            color: #ffffff !important;
            background-color: #4a5568 !important;
        }

        /* Smooth height transitions */
        .table tbody tr {
            transition: height 0.3s ease;
        }

        /* Allow table layout to adjust for content */
        .table {
            table-layout: auto;
            width: 100%;
        }

        /* Ensure body text color follows theme */
        body {
            color: var(--text-primary) !important;
        }

        /* Ensure all text elements follow theme */
        h1, h2, h3, h4, h5, h6, p, span, div, label {
            color: var(--text-primary);
        }

        /* Ensure main page header is white */
        .d-flex.align-items-center h3 {
            color: var(--text-primary) !important;
        }

        /* Ensure form elements are always visible */
        input:focus, textarea:focus, select:focus {
            color: var(--text-primary) !important;
            background: var(--input-bg) !important;
        }

        /* Ensure table content is always visible */
        .table td input,
        .table td textarea,
        .table td select {
            color: var(--text-primary) !important;
        }

        /* Ensure disabled elements are still visible */
        input:disabled, textarea:disabled, select:disabled {
            color: var(--text-primary) !important;
            opacity: 1 !important;
            background: var(--input-bg) !important;
        }

        /* Ensure readonly elements are visible */
        input:read-only, textarea:read-only {
            color: var(--text-primary) !important;
            background: var(--input-bg) !important;
        }

        /* Force visibility for all form elements after theme change */
        [data-theme] input,
        [data-theme] textarea,
        [data-theme] select {
            color: var(--text-primary) !important;
            background: var(--input-bg) !important;
            border-color: var(--input-border) !important;
        }

        /* Ensure select dropdown text is always visible */
        select {
            color: var(--text-primary) !important;
        }

        select option {
            color: var(--text-primary) !important;
            background: var(--input-bg) !important;
        }

        /* Searchable dropdown styles */
        .searchable-dropdown {
            position: relative;
            width: 100%;
        }

        .searchable-dropdown .dropdown-display {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-primary);
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
            font-size: 0.8rem;
        }

        .searchable-dropdown .dropdown-display:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 0.15rem rgba(99, 179, 237, 0.25);
            outline: none;
        }

        .searchable-dropdown .dropdown-display::after {
            content: '▼';
            font-size: 10px;
            margin-left: 5px;
        }

        .searchable-dropdown.open .dropdown-display::after {
            content: '▲';
        }

        .searchable-dropdown .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .searchable-dropdown.open .dropdown-menu {
            display: block;
        }

        .searchable-dropdown .search-input {
            width: 100%;
            padding: 16px;
            border: none;
            border-bottom: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 14px;
            height: 60px;
        }

        .searchable-dropdown .search-input:focus {
            outline: none;
            border-bottom-color: #63b3ed;
        }

        .searchable-dropdown .search-input::placeholder {
            color: var(--text-muted);
        }

        .searchable-dropdown .dropdown-item {
            padding: 16px 20px;
            cursor: pointer;
            color: var(--text-primary);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
            min-height: 50px;
        }

        .searchable-dropdown .dropdown-item:hover,
        .searchable-dropdown .dropdown-item.highlighted {
            background: rgba(99, 179, 237, 0.1);
        }

        .searchable-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }

        .searchable-dropdown .no-results {
            padding: 12px;
            text-align: center;
            color: var(--text-muted);
            font-style: italic;
        }

        /* Ensure proper height calculations */
        .table tbody {
            display: table-row-group;
        }

        .table tbody tr {
            display: table-row;
        }

        .table tbody td {
            display: table-cell;
            vertical-align: top;
        }

        /* Modal Theme Support */
        .modal-content {
            background: var(--bg-primary) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-light) !important;
        }

        .modal-header {
            background: var(--bg-secondary) !important;
            border-bottom: 1px solid var(--border-light) !important;
            color: var(--text-primary) !important;
        }

        .modal-header .modal-title {
            color: var(--text-primary) !important;
        }

        .modal-header .close {
            color: var(--text-primary) !important;
            opacity: 0.8;
        }

        .modal-header .close:hover {
            color: var(--text-primary) !important;
            opacity: 1;
        }

        .modal-body {
            background: var(--bg-primary) !important;
            color: var(--text-primary) !important;
        }

        .modal-footer {
            background: var(--bg-secondary) !important;
            border-top: 1px solid var(--border-light) !important;
        }

        /* Item Selection Modal Specific Styles */
        .item-checkbox-container {
            background: var(--bg-secondary) !important;
            border: 1px solid var(--border-light) !important;
            color: var(--text-primary) !important;
        }

        .item-checkbox-container:hover {
            background: rgba(var(--bg-accent-rgb, 102, 126, 234), 0.1) !important;
        }

        .item-checkbox-container .form-check-label {
            color: var(--text-primary) !important;
        }

        .item-checkbox-container .font-weight-bold {
            color: var(--text-primary) !important;
        }

        .item-checkbox-container .small.text-muted {
            color: var(--text-muted) !important;
        }

        .item-checkbox-container .small:not(.text-muted) {
            color: var(--text-primary) !important;
        }

        /* Checkbox styling for both themes */
        .form-check-input {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            width: 18px !important;
            height: 18px !important;
            margin-top: 2px !important;
        }

        .form-check-input:checked {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }

        .form-check-input:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }

        /* Ensure checkbox is visible in both themes */
        [data-theme="light"] .form-check-input {
            background-color: #ffffff !important;
            border: 2px solid #6c757d !important;
        }

        [data-theme="dark"] .form-check-input {
            background-color: rgba(255,255,255,0.1) !important;
            border: 2px solid rgba(255,255,255,0.3) !important;
        }

        [data-theme="light"] .form-check-input:checked {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }

        [data-theme="dark"] .form-check-input:checked {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }

        /* Search input styling */
        #itemSearchInput {
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: var(--text-primary) !important;
        }

        #itemSearchInput:focus {
            background: var(--input-bg) !important;
            border-color: #007bff !important;
            color: var(--text-primary) !important;
        }

        #itemSearchInput::placeholder {
            color: var(--text-muted) !important;
        }

        /* Button styling in modal */
        .modal .btn {
            color: var(--text-primary) !important;
        }

        .modal .btn-secondary {
            background: var(--bg-secondary) !important;
            border: 1px solid var(--border-light) !important;
        }

        .modal .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border: 1px solid #007bff !important;
        }

        .modal .btn-outline-primary {
            color: #007bff !important;
            border-color: #007bff !important;
            background: transparent !important;
        }

        .modal .btn-outline-primary:hover {
            background: #007bff !important;
            color: white !important;
        }

        .modal .btn-outline-secondary {
            color: var(--text-primary) !important;
            border-color: var(--border-light) !important;
            background: transparent !important;
        }

        .modal .btn-outline-secondary:hover {
            background: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
        }

        /* Selected count text */
        #selectedCount {
            color: var(--text-primary) !important;
        }

        /* Modal backdrop for dark theme */
        [data-theme="dark"] .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.8) !important;
        }

        [data-theme="light"] .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5) !important;
        }

        /* Ensure buttons are readable in view mode */
        body[data-view-mode] button:not([disabled]) {
            color: #ffffff !important;
            border-color: rgba(255,255,255,0.3) !important;
        }

        body[data-view-mode] .text-muted {
            color: var(--text-primary) !important;
        }

    </style>
</head>

<body class="g-sidenav-show" data-theme="light" style="background: var(--bg-primary); min-height: 100vh; transition: background-color 0.3s ease, color 0.3s ease;">

<?php include 'sidebar.php'; ?>

<main class="main-content position-relative border-radius-lg ps ps--active-y" style="margin-left: 280px; padding: 1.5rem;">
    <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <img src="assets/logo.svg" alt="PPMP Logo" style="width: 50px; height: 50px; margin-right: 15px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
            <div>
                <h3 class="text-primary mb-0">Project Procurement Management Plan</h3>
                <small class="text-muted">Manage your procurement planning efficiently</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Theme Toggle Button -->
            <button class="btn btn-outline-secondary" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
            <div class="btn-group">
                <button class="btn btn-outline-success" onclick="savePPMP('draft', 'saveDraftBtn')" id="saveDraftBtn">
                    <i class="fas fa-save"></i> Save Draft
                </button>
                <button class="btn btn-success" onclick="savePPMP('submitted', 'submitBtn')" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
                <button class="btn btn-info" onclick="loadPPMP()" id="loadBtn">
                    <i class="fas fa-folder-open"></i> Load PPMP
                </button>
                <button class="btn btn-warning" onclick="newPPMP()" id="newBtn">
                    <i class="fas fa-plus"></i> New PPMP
                </button>
            </div>
        </div>
    </div>

    <!-- PPMP Details Section -->
    <div class="form-section">
        <div class="section-title">PPMP Details</div>
        <div class="row mb-2">
            <div class="col-md-3">
                <label for="ppmp_number" class="form-label">PPMP Number</label>
                <input type="text" class="form-control" id="ppmp_number">
            </div>
            <div class="col-md-2">
                <label class="form-label">Plan Year</label>
                <input type="number" class="form-control" id="plan_year" min="2020" max="2030" value="<?php echo date('Y') + 1; ?>" style="max-width: 120px;">
            </div>
            <div class="col-md-3">
                <label class="form-label">Classification</label>
                <input type="text" class="form-control" value="ANNUAL" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Position</label>
                <input type="text" class="form-control" value="ADMINISTRATIVE AIDE">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4">
                <label class="form-label">Department</label>
                <input type="text" class="form-control" id="department" value="<?php echo htmlspecialchars($user_department); ?>" placeholder="Auto-populated from user department" readonly>
                <!-- DEBUG: Final department value: <?php echo htmlspecialchars($user_department); ?> -->
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact Person</label>
                <input type="text" class="form-control" id="contact_person" value="<?php echo htmlspecialchars($user_contact); ?>" placeholder="Auto-populated from username">
                <!-- DEBUG: Final contact value: <?php echo htmlspecialchars($user_contact); ?> -->
            </div>
            <div class="col-md-4">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" value="MALAYBALAY CITY">
            </div>
        </div>
    </div>

    <!-- Item List Section -->
    <div class="form-section">
        <div class="section-title">PPMP Items</div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="addRow()">➕ Add Row</button>
                <button class="btn btn-primary" onclick="openItemSelectionModal()" title="Press F3 to open item selection" id="f3Btn">
                    <i class="fas fa-list"></i> Select Items (F3)
                </button>
            </div>
            <small class="text-muted">Scroll horizontally if needed</small>
        </div>
        <div class="table-responsive">
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th title="Select Item Code">Item Code</th>
                    <th title="Item Description">Items Description</th>
                    <th title="Unit of Measure">Unit</th>
                    <th>Jan</th><th>Feb</th><th>Mar</th><th>Q1</th>
                    <th>Apr</th><th>May</th><th>Jun</th><th>Q2</th>
                    <th>Jul</th><th>Aug</th><th>Sep</th><th>Q3</th>
                    <th>Oct</th><th>Nov</th><th>Dec</th><th>Q4</th>
                    <th>Unit Cost</th>
                    <th>Total Qty</th>
                    <th>Total Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="itemsTableBody">
                    <!-- Rows go here -->
                </tbody>
        </table>
        </div>
        
    <div class="d-flex justify-content-between align-items-center mt-2">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-primary" id="prevPage" onclick="changePage(-1)" disabled>⬅️ Prev</button>
        <span class="text-muted small">
            Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
            (<span id="total_items">0</span> items)
        </span>
        <button class="btn btn-sm btn-primary" id="nextPage" onclick="changePage(1)">Next ➡️</button>
    </div>
    <div class="text-muted small">
        💡 Tip: Use horizontal scroll for full view
    </div>
</div>

    </div>

    <!-- Item Selection Modal -->
    <div class="modal fade" id="itemSelectionModal" tabindex="-1" role="dialog" aria-labelledby="itemSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="background: var(--bg-primary); color: var(--text-primary);">
                <div class="modal-header" style="background: var(--bg-secondary); border-bottom: 1px solid var(--border-light);">
                    <h5 class="modal-title" id="itemSelectionModalLabel">Select Items for PPMP</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary);">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="itemSearchInput" placeholder="Search items..." style="background: var(--input-bg); color: var(--text-primary); border: 1px solid var(--input-border);">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllBtn">Clear All</button>
                            </div>
                            <div>
                                <small class="text-muted">Selected: <span id="selectedCount">0</span> items</small>
                            </div>
                        </div>
                    </div>
                    <div id="itemsListContainer" style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border-light); border-radius: 4px; padding: 10px;">
                        <!-- Items will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--bg-secondary); border-top: 1px solid var(--border-light);">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="addSelectedItemsBtn" style="color: white !important;">Add Selected Items</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Totals -->
    <div class="form-section">
        <div class="row">
            <div class="col-md-4">
                <strong>Total Items:</strong> <span id="total_items">0</span>
            </div>
            <div class="col-md-4">
                <strong>Total Cost:</strong> ₱<span id="grand_total">0.00</span>
            </div>
        </div>
    </div>
</div>
</main>

<!-- jQuery (required for Bootstrap modals) -->
<script src="assets/jquery-3.6.0.min.js"></script>

<!-- Argon Core JS -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="argondashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="argondashboard/assets/js/argon-dashboard.min.js"></script>

<script src="../apiPPMP/api_config.js.php"></script>

<script src="js/ppmp.js"></script>

<script>
// Helper functions for authentication (no expiry check)
function isLoggedIn() {
    const token = localStorage.getItem('access_token');
    return !!token; // Just check if token exists
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

</script>


// User data will be populated by token manager

<script>
// PPMP initialization - authentication handled by PHP
document.addEventListener('DOMContentLoaded', function() {
    console.log('PPMP page loaded successfully');

    // Populate user data from PHP variables
    const departmentField = document.getElementById('department');
    const contactField = document.getElementById('contact_person');

    if (departmentField) {
        departmentField.value = '<?php echo htmlspecialchars($department); ?>' || '';
    }
    if (contactField) {
        contactField.value = '<?php echo htmlspecialchars($firstname . ' ' . ($middlename ?? '') . ' ' . $lastname . ' ' . ($name_ext ?? '')); ?>' || '<?php echo htmlspecialchars($username); ?>';
    }

    // Initialize theme manager
    new PPMPThemeManager();

    // Handle URL parameters for view/edit modes
    const urlParams = new URLSearchParams(window.location.search);
    const viewId = urlParams.get('view');
    const editId = urlParams.get('edit');

    if (viewId) {
        // Load PPMP in view-only mode
        loadPPMPForView(viewId);
    } else if (editId) {
        // Load PPMP for editing
        loadPPMPForEdit(editId);
    } else {
        // Normal mode - ensure view mode attribute is removed
        document.body.removeAttribute('data-view-mode');
    }
});

function loadPPMPForView(ppmpId) {
    // Ensure items are loaded first, then load PPMP data
    if (itemsList.length === 0) {
        // Items not loaded yet, load them first
        loadItems().then(() => {
            loadPPMPDataForView(ppmpId);
        });
    } else {
        // Items already loaded, proceed directly
        loadPPMPDataForView(ppmpId);
    }
}

function loadPPMPDataForView(ppmpId) {
    // Load the PPMP data first
    const token = localStorage.getItem('access_token');
    fetch(`${API_BASE_URL}/api_load_ppmp.php?id=${ppmpId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populatePPMPForm(data.ppmp);

            // After populating, disable all form inputs and buttons for view mode
            setTimeout(() => {
                document.querySelectorAll('input, select, textarea, button').forEach(el => {
                    // Keep pagination buttons and save button enabled
                    if (el.type !== 'button' || (!el.onclick) ||
                        (!el.onclick.toString().includes('savePPMP') &&
                         !el.onclick.toString().includes('changePage') &&
                         !el.id.includes('prevPage') &&
                         !el.id.includes('nextPage'))) {
                        el.disabled = true;
                        // Ensure disabled elements maintain proper styling
                        el.style.color = 'var(--text-primary)';
                        el.style.opacity = '1';
                    }
                });

                // Disable dropdown displays specifically
                document.querySelectorAll('.searchable-dropdown .dropdown-display').forEach(display => {
                    display.style.pointerEvents = 'none';
                    display.style.opacity = '1';
                    display.style.color = 'var(--text-primary)';
                    display.style.cursor = 'default';
                    display.style.backgroundColor = 'var(--input-bg)';
                });

                // Ensure enabled buttons have proper white text color
                document.querySelectorAll('button:not([disabled])').forEach(button => {
                    if (button.id.includes('prevPage') || button.id.includes('nextPage') ||
                        button.onclick?.toString().includes('changePage') ||
                        button.onclick?.toString().includes('savePPMP')) {
                        button.style.color = '#ffffff !important';
                        button.style.opacity = '1';
                        button.style.borderColor = 'rgba(255,255,255,0.3)';
                    }
                });

                // Also ensure pagination info text is visible
                const paginationText = document.querySelector('.text-muted.small');
                if (paginationText) {
                    paginationText.style.color = 'var(--text-primary)';
                }

                // Manually populate descriptions for disabled dropdowns
                const populateViewModeData = () => {
                    const rows = document.querySelectorAll('#itemsTableBody tr');
                    rows.forEach((row, index) => {
                        const entry = data.ppmp.entries[index];
                        if (entry) {
                            // Set description from loaded data
                            const descTextarea = row.querySelector('.item-description');
                            const unitInput = row.querySelector('.item-unit');
                            const costInput = row.querySelector('.unit_cost');
                            const dropdown = row.querySelector('.searchable-dropdown');
                            const selectedText = dropdown ? dropdown.querySelector('.selected-text') : null;

                            if (descTextarea) descTextarea.value = entry.Item_Description || entry.item_description || '';
                            if (unitInput) unitInput.value = entry.Unit || entry.unit || '';
                            if (costInput) costInput.value = entry.Unit_Cost || entry.unit_cost || 0;

                            // Set selected text for dropdown
                            if (selectedText && entry.item_id) {
                                const item = itemsList.find(item => parseInt(item.ID) === parseInt(entry.item_id));
                                if (item) {
                                    selectedText.textContent = item.Item_Code ? '[' + item.Item_Code + '] ' + item.Items_Description : item.Items_Description;
                                    // Also set the data attributes for the dropdown
                                    dropdown.setAttribute('data-selected-item', entry.item_id);
                                    dropdown.setAttribute('data-code', item.Item_Code || '');
                                    dropdown.setAttribute('data-name', item.Item_Name || '');
                                    dropdown.setAttribute('data-category', item.Category || '');
                                }
                            }
                        }
                    });
                };

                // Try to populate immediately, if dropdowns aren't ready yet, try again
                populateViewModeData();
                setTimeout(populateViewModeData, 100); // Additional attempt after short delay

                // Update item count after populating descriptions
                updateItemCount();
            }, 300); // Increased delay to ensure dropdown is fully initialized

            document.getElementById('saveDraftBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('newBtn').style.display = 'none';
            document.querySelector('h3').textContent = 'View PPMP Document';

            // Add view mode attribute to body for CSS styling
            document.body.setAttribute('data-view-mode', 'true');
        } else {
            alert('Error loading PPMP: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Load error:', error);
        alert('Error loading PPMP for viewing');
    });
}

function loadPPMPForEdit(ppmpId) {
    // Ensure items are loaded first, then load PPMP data
    if (itemsList.length === 0) {
        // Items not loaded yet, load them first
        loadItems().then(() => {
            loadPPMPDataForEdit(ppmpId);
        });
    } else {
        // Items already loaded, proceed directly
        loadPPMPDataForEdit(ppmpId);
    }
}

function loadPPMPDataForEdit(ppmpId) {
    // Load the PPMP data for editing
    const token = localStorage.getItem('access_token');
    fetch(`${API_BASE_URL}/api_load_ppmp.php?id=${ppmpId}`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const ppmpStatus = data.ppmp.header.Status || data.ppmp.header.status;

            populatePPMPForm(data.ppmp);
            currentPPMPId = data.ppmp.header.ID || data.ppmp.header.id;
            currentPPMPNumber = data.ppmp.header.PPMP_Number || data.ppmp.header.ppmp_number;

            // Update button visibility based on status
            if (ppmpStatus === 'submitted') {
                // Hide Update Draft button for submitted PPMPs
                document.getElementById('saveDraftBtn').style.display = 'none';
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-paper-plane"></i> Update & Resubmit';
            } else {
                // Show Update Draft button for draft PPMPs
                document.getElementById('saveDraftBtn').style.display = 'inline-block';
                document.getElementById('saveDraftBtn').innerHTML = '<i class="fas fa-save"></i> Update Draft';
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-paper-plane"></i> Update & Submit';
            }

            // Allow editing PPMP number when editing existing PPMP
            document.getElementById('ppmp_number').readOnly = false;

            // Remove view mode attribute if it exists
            document.body.removeAttribute('data-view-mode');
        } else {
            alert('Error loading PPMP: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Load error:', error);
        alert('Error loading PPMP for editing');
    });
}

// Theme Management for PPMP Page
class PPMPThemeManager {
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

        // Force update select elements styling
        const selects = document.querySelectorAll('select');
        selects.forEach(select => {
            if (theme === 'dark') {
                select.style.color = '#ffffff';
                select.style.backgroundColor = 'rgba(255,255,255,0.1)';
                select.style.borderColor = 'rgba(255,255,255,0.2)';
                // Force update options styling
                const options = select.querySelectorAll('option');
                options.forEach(option => {
                    option.style.color = '#ffffff';
                    option.style.backgroundColor = '#2d3748';
                });
            } else {
                select.style.color = '#1a202c';
                select.style.backgroundColor = 'rgba(255,255,255,0.8)';
                select.style.borderColor = 'rgba(0,0,0,0.2)';
                // Force update options styling
                const options = select.querySelectorAll('option');
                options.forEach(option => {
                    option.style.color = '#1a202c';
                    option.style.backgroundColor = '#ffffff';
                });
            }
        });

        // Update searchable dropdowns
        const dropdowns = document.querySelectorAll('.searchable-dropdown');
        dropdowns.forEach(dropdown => {
            const display = dropdown.querySelector('.dropdown-display');
            const searchInput = dropdown.querySelector('.search-input');
            const items = dropdown.querySelectorAll('.dropdown-item');

            if (theme === 'dark') {
                if (display) {
                    display.style.color = '#ffffff';
                    display.style.backgroundColor = 'rgba(255,255,255,0.1)';
                    display.style.borderColor = 'rgba(255,255,255,0.2)';
                }
                if (searchInput) {
                    searchInput.style.color = '#ffffff';
                    searchInput.style.backgroundColor = 'rgba(255,255,255,0.1)';
                    searchInput.style.borderColor = 'rgba(255,255,255,0.2)';
                }
                items.forEach(item => {
                    item.style.color = '#ffffff';
                });
            } else {
                if (display) {
                    display.style.color = '#1a202c';
                    display.style.backgroundColor = 'rgba(255,255,255,0.8)';
                    display.style.borderColor = 'rgba(0,0,0,0.2)';
                }
                if (searchInput) {
                    searchInput.style.color = '#1a202c';
                    searchInput.style.backgroundColor = 'rgba(255,255,255,0.8)';
                    searchInput.style.borderColor = 'rgba(0,0,0,0.2)';
                }
                items.forEach(item => {
                    item.style.color = '#1a202c';
                });
            }
        });

        // Update modal theme if it's open
        const modal = document.getElementById('itemSelectionModal');
        if (modal && modal.classList.contains('show')) {
            // Force theme application to modal
            setTimeout(() => {
                if (typeof applyThemeToModal === 'function') {
                    applyThemeToModal();
                }
            }, 100);
        }

        // Small delay to ensure CSS variables are applied
        setTimeout(() => {
            // Force a repaint by accessing offsetHeight
            document.body.offsetHeight;
        }, 10);
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
</script>

</body>
</html>
