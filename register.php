<?php

if (isset(null)) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPMP System | Register</title>

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
            --login-bg: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --login-card-bg: rgba(255, 255, 255, 0.95);
            --login-text-primary: #1a202c;
            --login-text-muted: #718096;
            --login-border: rgba(255, 255, 255, 0.2);
            --login-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            --login-accent: #1e40af;
        }

        [data-theme="dark"] {
            --login-bg: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --login-card-bg: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            --login-text-primary: #ffffff;
            --login-text-muted: #a0aec0;
            --login-border: rgba(255, 255, 255, 0.1);
            --login-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: var(--login-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            animation: float 20s infinite linear;
        }

        .bg-shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-shape:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 60%;
            right: 10%;
            animation-delay: 5s;
        }

        .bg-shape:nth-child(3) {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
        }

        .bg-shape:nth-child(4) {
            width: 250px;
            height: 250px;
            top: 20%;
            right: 20%;
            animation-delay: 15s;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(10px) rotate(240deg); }
            100% { transform: translateY(0px) rotate(360deg); }
        }

        /* Registration Container */
        .register-container {
            width: 100%;
            max-width: 800px;
            min-height: 700px;
            display: flex;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--login-shadow);
            backdrop-filter: blur(20px);
            border: 1px solid var(--login-border);
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 1s ease, transform 1s ease;
        }

        .register-container.fade-in {
            opacity: 1;
            transform: translateY(0);
        }

        /* Left Side - Branding */
        .register-branding {
            flex: 0.6;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.9) 0%, rgba(30, 58, 138, 0.9) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            position: relative;
        }

        .register-branding::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
            color: #ffffff !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .brand-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            text-align: center;
            margin-bottom: 40px;
            color: #ffffff !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .brand-features {
            text-align: center;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1rem;
            color: #ffffff !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .feature-item i {
            margin-right: 10px;
            color: #68d391;
        }

        /* Right Side - Registration Form */
        .register-form-container {
            flex: 1.4;
            background: var(--login-card-bg);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--login-text-primary);
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: var(--login-text-muted);
            font-size: 1rem;
        }

        /* Theme Toggle */
        .theme-toggle-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            z-index: 10;
        }

        .theme-toggle-btn:hover {
            transform: scale(1.1);
            background: rgba(255,255,255,0.3);
        }

        /* Light theme adjustments for theme toggle */
        [data-theme="light"] .theme-toggle-btn {
            background: rgba(0,0,0,0.1);
            border: 2px solid rgba(0,0,0,0.2);
            color: #1e3a8a;
        }

        [data-theme="light"] .theme-toggle-btn:hover {
            background: rgba(0,0,0,0.2);
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--login-text-primary);
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
        }

        .form-group.username-group::before {
            content: "\f007"; /* fa-user */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--login-text-muted);
            z-index: 2;
            font-size: 1rem;
        }

        .form-group.password-group::before {
            content: "\f023"; /* fa-lock */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--login-text-muted);
            z-index: 2;
            font-size: 1rem;
        }

        .form-group.password-group .form-control {
            padding-right: 45px;
        }

        .form-control:focus {
            border-color: var(--login-accent, #1e40af);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
            background: white;
            outline: none;
        }

        /* Light theme form control adjustments */
        [data-theme="light"] .form-control {
            background: rgba(255,255,255,0.9);
            border: 2px solid #e2e8f0;
            color: #1a202c;
        }

        [data-theme="light"] .form-control:focus {
            background: white;
            border-color: #1e40af;
        }

        [data-theme="light"] .form-control::placeholder {
            color: #a0aec0;
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        /* Dark theme select styling */
        [data-theme="dark"] .form-control {
            color: #ffffff;
            background: #374151;
        }

        [data-theme="dark"] .form-control option {
            color: #ffffff;
            background: #374151;
        }

        [data-theme="light"] .form-control option {
            color: #1a202c;
            background: #ffffff;
        }

        /* Password toggle button */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--login-text-muted);
            cursor: pointer;
            font-size: 1rem;
            z-index: 2;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--login-accent);
        }

        /* Buttons */
        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.3);
        }

        /* Links */
        .form-links {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-links a {
            color: #1e40af;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .form-links a:hover {
            color: #1e3a8a;
        }

        /* Login Link */
        .login-link {
            text-align: center;
            color: var(--login-text-muted);
        }

        .login-link a {
            color: #1e40af;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            color: #1e3a8a;
        }

        /* Error Messages */
        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #feb2b2;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
                min-height: auto;
            }

            .register-branding {
                padding: 40px 30px;
                order: 2;
            }

            .register-form-container {
                padding: 30px 20px;
                order: 1;
            }

            .brand-title {
                font-size: 2rem;
            }
        }

        /* Profile Picture Upload Styles */
        .profile-upload-section {
            text-align: center;
            padding: 20px;
            border: 2px dashed var(--login-border);
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .profile-upload-section:hover {
            border-color: var(--login-accent);
            background: rgba(30, 64, 175, 0.05);
        }

        .profile-upload-btn {
            background: var(--login-accent);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .profile-upload-btn:hover {
            background: var(--login-bg);
            transform: translateY(-2px);
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body data-theme="light">

<!-- Animated Background -->
<div class="background-animation">
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
</div>

<div class="register-container">
    <!-- Left Side - Branding -->
    <div class="register-branding">
        <div class="brand-logo">
            <img src="assets/logo.svg" alt="PPMP Logo" style="width: 80px; height: 80px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
        </div>
        <h1 class="brand-title">Join PPMP System</h1>
        <p class="brand-subtitle">Create your account to get started</p>

        <div class="brand-features">
            <div class="feature-item">
                <i class="fas fa-user-plus"></i>
                <span>Easy Account Creation</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-shield-alt"></i>
                <span>Secure Registration</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-rocket"></i>
                <span>Quick Setup Process</span>
            </div>
        </div>
    </div>

    <!-- Right Side - Registration Form -->
    <div class="register-form-container">
        <!-- Theme Toggle -->
        <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>

        <div class="form-header">
            <h2 class="form-title">Create Account</h2>
            <p class="form-subtitle">Fill in your information to register</p>
        </div>

        <!-- Error Message -->
        <div class="error-message" id="error-message" style="display: none;"></div>

        <!-- Registration Form -->
        <form id="registerForm">
            <!-- Name Fields -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="regFirstname" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="regFirstname" name="firstname" placeholder="Enter your first name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="regMiddlename" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="regMiddlename" name="middlename" placeholder="Enter your middle name (optional)">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="regLastname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="regLastname" name="lastname" placeholder="Enter your last name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="regNameExt" class="form-label">Name Extension</label>
                    <input type="text" class="form-control" id="regNameExt" name="name_ext" placeholder="Jr., Sr., III, etc. (optional)">
                </div>
            </div>

            <!-- Account Fields -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="regUsername" class="form-label">Username</label>
                    <div class="form-group username-group">
                        <input type="text" class="form-control" id="regUsername" name="username" placeholder="Choose a username" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="regRole" class="form-label">Role</label>
                    <select class="form-control" id="regRole" name="role" required>
                        <option value="user">User</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="regDepartment" class="form-label">Department</label>
                <select class="form-control" id="regDepartment" name="department_id" required>
                    <option value="">Select Department</option>
                    <!-- Departments will be loaded dynamically -->
                </select>
            </div>
            <div class="mb-3">
                <label for="regPassword" class="form-label">Password</label>
                <div class="form-group password-group">
                    <input type="password" class="form-control" id="regPassword" name="password" placeholder="Create a strong password" required>
                    <button type="button" class="password-toggle" data-target="regPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small style="color: var(--login-text-muted);">Minimum 6 characters</small>
            </div>
            <div class="mb-3">
                <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                <div class="form-group password-group">
                    <input type="password" class="form-control" id="regConfirmPassword" placeholder="Confirm your password" required>
                    <button type="button" class="password-toggle" data-target="regConfirmPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Profile Picture Upload -->
            <div class="mb-3">
                <label class="form-label">Profile Picture (Optional)</label>
                <div class="profile-upload-section">
                    <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                    <p class="mb-2">Upload a profile picture</p>
                    <input type="file" id="regProfilePicture" accept="image/*" style="display: none;">
                    <button type="button" class="profile-upload-btn" onclick="document.getElementById('regProfilePicture').click()">
                        <i class="fas fa-upload me-2"></i>Choose File
                    </button>
                    <div id="profilePreview" class="mt-2" style="display: none;">
                        <img id="profilePreviewImg" src="" alt="Profile Preview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--login-accent);">
                    </div>
                </div>
                <small style="color: var(--login-text-muted);">JPG, PNG, GIF or WebP. Max 5MB.</small>
            </div>

            <button type="submit" class="btn btn-register" id="registerBtn">
                <span id="btnText">Create Account</span>
                <span class="loading" id="btnLoading" style="display: none;"></span>
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="../api_config.js.php"></script>

<script>
// Theme Management for Register Page
class RegisterThemeManager {
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

        const root = document.documentElement;
        if (theme === 'dark') {
            root.style.setProperty('--login-bg', 'linear-gradient(135deg, #1a202c 0%, #2d3748 100%)');
            root.style.setProperty('--login-card-bg', 'linear-gradient(135deg, #2d3748 0%, #1a202c 100%)');
            root.style.setProperty('--login-text-primary', '#ffffff');
            root.style.setProperty('--login-text-muted', '#a0aec0');
            root.style.setProperty('--login-border', 'rgba(255,255,255,0.1)');
        } else {
            root.style.setProperty('--login-bg', 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)');
            root.style.setProperty('--login-card-bg', 'rgba(255, 255, 255, 0.95)');
            root.style.setProperty('--login-text-primary', '#1a202c');
            root.style.setProperty('--login-text-muted', '#718096');
            root.style.setProperty('--login-border', 'rgba(255, 255, 255, 0.2)');
        }
    }

    updateToggleIcon() {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            if (this.currentTheme === 'dark') {
                icon.className = 'fas fa-sun';
                icon.parentElement.title = 'Switch to Light Theme';
            } else {
                icon.className = 'fas fa-moon';
                icon.parentElement.title = 'Switch to Dark Theme';
            }
        }
    }

    saveTheme() {
        localStorage.setItem('ppmp-theme', this.currentTheme);
    }
}

// Registration Form Handler
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme manager
    new RegisterThemeManager();

    // Setup form handlers
    const registerForm = document.getElementById('registerForm');

    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }

    // Load departments
    loadDepartments();

    // Setup profile picture preview
    setupProfilePicturePreview();

    // Setup password toggles
    setupPasswordToggles();

    // Fade in container
    setTimeout(() => {
        document.querySelector('.register-container').classList.add('fade-in');
    }, 100);
});

async function loadDepartments() {
    try {
        const response = await fetch(`${API_BASE_URL}/api_get_departments.php`);
        const data = await response.json();

        const departmentSelect = document.getElementById('regDepartment');
        if (departmentSelect && data.status === 'success') {
            departmentSelect.innerHTML = '<option value="">Select Department</option>';

            data.data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.ID;
                option.textContent = dept.Department;
                departmentSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

function setupProfilePicturePreview() {
    const fileInput = document.getElementById('regProfilePicture');
    const previewDiv = document.getElementById('profilePreview');
    const previewImg = document.getElementById('profilePreviewImg');

    if (fileInput && previewDiv && previewImg) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, or WebP).');
                    fileInput.value = '';
                    previewDiv.style.display = 'none';
                    return;
                }

                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB.');
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
}

async function handleRegister(event) {
    event.preventDefault();

    const firstname = document.getElementById('regFirstname').value.trim();
    const middlename = document.getElementById('regMiddlename').value.trim();
    const lastname = document.getElementById('regLastname').value.trim();
    const nameExt = document.getElementById('regNameExt').value.trim();
    const username = document.getElementById('regUsername').value.trim();
    const password = document.getElementById('regPassword').value.trim();
    const confirmPassword = document.getElementById('regConfirmPassword').value.trim();
    const role = document.getElementById('regRole').value;
    const departmentId = document.getElementById('regDepartment').value;
    const profilePicture = document.getElementById('regProfilePicture').files[0];

    // Validate inputs
    if (!firstname || !lastname || !username || !password || !confirmPassword || !departmentId) {
        showError('Please fill in all required fields.');
        return;
    }

    if (password !== confirmPassword) {
        showError('Passwords do not match.');
        return;
    }

    if (password.length < 6) {
        showError('Password must be at least 6 characters long.');
        return;
    }

    // Validate profile picture if selected
    if (profilePicture) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(profilePicture.type)) {
            showError('Please select a valid image file (JPG, PNG, GIF, or WebP).');
            return;
        }
        if (profilePicture.size > 5 * 1024 * 1024) {
            showError('Profile picture must be less than 5MB.');
            return;
        }
    }

    // Show loading state
    const registerBtn = document.getElementById('registerBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');

    registerBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-block';

    try {
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('firstname', firstname);
        formData.append('middlename', middlename || '');
        formData.append('lastname', lastname);
        formData.append('name_ext', nameExt || '');
        formData.append('username', username);
        formData.append('password', password);
        formData.append('role', role);
        formData.append('department_id', parseInt(departmentId));

        if (profilePicture) {
            formData.append('profile_picture', profilePicture);
        }

        const response = await fetch(`${API_BASE_URL}/api_register.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'success') {
            alert('Registration successful! You can now log in.');
            window.location.href = 'login.php';
        } else {
            showError(data.message || 'Registration failed. Please try again.');
        }
    } catch (error) {
        console.error('Registration error:', error);
        showError('Network error. Please check your connection and try again.');
    } finally {
        // Reset loading state
        registerBtn.disabled = false;
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    }
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

function showError(message) {
    const errorMessage = document.getElementById('error-message');
    errorMessage.textContent = message;
    errorMessage.style.display = 'block';
}
</script>

</body>
</html>
