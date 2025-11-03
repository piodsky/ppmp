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
    <title>PPMP System | Login</title>

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

        /* Loading screen theme variables */
        [data-theme="dark"] .loading-screen {
            --loading-bg: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --loading-text: #ffffff;
            --loading-accent: #63b3ed;
        }

        [data-theme="light"] .loading-screen,
        .loading-screen {
            --loading-bg: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --loading-text: #ffffff;
            --loading-accent: #667eea;
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

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 1200px;
            min-height: 600px;
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

        .login-container.fade-in {
            opacity: 1;
            transform: translateY(0);
        }

        /* Left Side - Branding */
        .login-branding {
            flex: 1;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.9) 0%, rgba(30, 58, 138, 0.9) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            position: relative;
        }

        .login-branding::before {
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

        /* Right Side - Login Form */
        .login-form-container {
            flex: 1;
            background: var(--login-card-bg);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
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
            margin-bottom: 25px;
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
            padding: 15px 20px 15px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
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

        /* Password toggle button */
        .password-toggle {
            position: absolute;
            right: 10px;
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

        .form-group.password-group .form-control {
            padding-right: 50px;
        }

        /* Buttons */
        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.3);
        }

        .btn-google {
            width: 100%;
            padding: 15px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            color: #718096;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .btn-google:hover {
            border-color: #cbd5e0;
            background: #f8fafc;
            transform: translateY(-1px);
        }

        /* Links */
        .form-links {
            text-align: center;
            margin-bottom: 30px;
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

        /* Register Link */
        .register-link {
            text-align: center;
            color: var(--login-text-muted);
        }

        .register-link a {
            color: #1e40af;
            font-weight: 600;
            text-decoration: none;
        }

        .register-link a:hover {
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
            .login-container {
                flex-direction: column;
                min-height: auto;
            }

            .login-branding {
                padding: 40px 30px;
                order: 2;
            }

            .login-form-container {
                padding: 40px 30px;
                order: 1;
            }

            .brand-title {
                font-size: 2rem;
            }
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

        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--loading-bg, var(--login-bg));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 1s ease, visibility 1s ease;
            overflow: hidden;
        }

        /* Loading Screen Theme Toggle */
        .loading-theme-toggle {
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
            z-index: 10000;
        }

        .loading-theme-toggle:hover {
            transform: scale(1.1);
            background: rgba(255,255,255,0.3);
        }

        /* Light theme loading screen toggle */
        [data-theme="light"] .loading-theme-toggle {
            background: rgba(0,0,0,0.1);
            border: 2px solid rgba(0,0,0,0.2);
            color: #1e3a8a;
        }

        [data-theme="light"] .loading-theme-toggle:hover {
            background: rgba(0,0,0,0.2);
        }

        .loading-screen.fade-out {
            opacity: 0;
            visibility: hidden;
        }

        /* Animated particles */
        .loading-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255,255,255,0.6);
            border-radius: 50%;
            animation: particleFloat 8s infinite linear;
        }

        .particle:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { top: 60%; left: 80%; animation-delay: 2s; }
        .particle:nth-child(3) { top: 40%; left: 60%; animation-delay: 4s; }
        .particle:nth-child(4) { top: 80%; left: 30%; animation-delay: 6s; }
        .particle:nth-child(5) { top: 10%; left: 70%; animation-delay: 1s; }

        @keyframes particleFloat {
            0% { transform: translateY(0px) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        .loading-content {
            text-align: center;
            z-index: 2;
            animation: contentFadeIn 1s ease-out;
        }

        @keyframes contentFadeIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .loading-logo {
            width: 300px;
            height: 300px;
            margin-bottom: 30px;
            border-radius: 20px;
            box-shadow:
                0 15px 35px rgba(0,0,0,0.3),
                0 25px 65px rgba(0,0,0,0.2),
                0 0 0 2px rgba(255,255,255,0.1) inset,
                0 0 30px rgba(102,126,234,0.4);
            animation: logoEntrance 1s ease-out, logoFalling 4s ease-in-out 1s infinite, logo3DRotate 6s ease-in-out 1s infinite, logoPulse 3s ease-in-out 1s infinite;
            transition: transform 0.3s ease;
            transform-style: preserve-3d;
            position: relative;
        }

        .loading-logo:hover {
            transform: scale(1.1);
            animation-play-state: paused;
        }

        @keyframes logoEntrance {
            0% { transform: scale(0) rotate(-180deg); opacity: 0; }
            50% { transform: scale(1.2) rotate(-90deg); opacity: 0.7; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1) rotateX(0deg) rotateY(0deg); filter: brightness(1); }
            50% { transform: scale(1.05) rotateX(2deg) rotateY(2deg); filter: brightness(1.1); }
        }

        @keyframes logoFalling {
            0% { transform: translateY(-40px) scale(0.9); opacity: 0.8; }
            20% { transform: translateY(0px) scale(1.1); opacity: 1; }
            40% { transform: translateY(-15px) scale(1.03); opacity: 1; }
            60% { transform: translateY(0px) scale(1.01); opacity: 1; }
            80% { transform: translateY(-8px) scale(1.005); opacity: 1; }
            100% { transform: translateY(0px) scale(1); opacity: 1; }
        }

        @keyframes logo3DRotate {
            0% { transform: rotateY(0deg); }
            25% { transform: rotateY(90deg); }
            50% { transform: rotateY(180deg); }
            75% { transform: rotateY(270deg); }
            100% { transform: rotateY(360deg); }
        }

        @keyframes logoFalling {
            0% { transform: translateY(-50px) scale(0.8); opacity: 0.7; }
            20% { transform: translateY(0px) scale(1.1); opacity: 1; }
            40% { transform: translateY(-20px) scale(1.05); opacity: 1; }
            60% { transform: translateY(0px) scale(1.02); opacity: 1; }
            80% { transform: translateY(-10px) scale(1.01); opacity: 1; }
            100% { transform: translateY(0px) scale(1); opacity: 1; }
        }

        .loading-text {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            animation: textGlow 3s ease-in-out infinite alternate;
        }

        /* Light theme loading text */
        [data-theme="light"] .loading-text {
            color: #1e3a8a;
            text-shadow: 2px 2px 4px rgba(255,255,255,0.8);
        }

        @keyframes textGlow {
            0% { filter: drop-shadow(0 0 5px rgba(255,255,255,0.3)); }
            100% { filter: drop-shadow(0 0 20px rgba(255,255,255,0.8)); }
        }

        .loading-subtitle {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.9);
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            animation: subtitleFade 2s ease-out 0.5s both;
        }

        /* Light theme loading subtitle */
        [data-theme="light"] .loading-subtitle {
            color: rgba(30, 58, 138, 0.9);
            text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
        }

        @keyframes subtitleFade {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .progress-container {
            width: 300px;
            height: 4px;
            background: rgba(255,255,255,0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #1e40af, #1e3a8a);
            border-radius: 2px;
            width: 0%;
            transition: width 0.1s ease;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }

        /* Light theme progress bar */
        [data-theme="light"] .progress-bar {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            box-shadow: 0 0 10px rgba(30, 64, 175, 0.5);
        }

        .loading-dots {
            display: inline-block;
            margin-top: 20px;
        }

        .loading-dots span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            margin: 0 4px;
            animation: dotBounce 1.4s ease-in-out infinite both;
        }

        .loading-dots span:nth-child(1) { animation-delay: -0.32s; }
        .loading-dots span:nth-child(2) { animation-delay: -0.16s; }
        .loading-dots span:nth-child(3) { animation-delay: 0s; }

        @keyframes dotBounce {
            0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }


        /* Enhanced particle effects */
        .particle:nth-child(1) { background: linear-gradient(45deg, rgba(255,255,255,0.8), rgba(102,126,234,0.6)); }
        .particle:nth-child(2) { background: linear-gradient(45deg, rgba(255,255,255,0.8), rgba(118,75,162,0.6)); }
        .particle:nth-child(3) { background: linear-gradient(45deg, rgba(102,126,234,0.6), rgba(255,255,255,0.8)); }
        .particle:nth-child(4) { background: linear-gradient(45deg, rgba(118,75,162,0.6), rgba(255,255,255,0.8)); }
        .particle:nth-child(5) { background: linear-gradient(45deg, rgba(255,255,255,0.8), rgba(99,179,237,0.6)); }

        /* Light theme particle effects */
        [data-theme="light"] .particle:nth-child(1) { background: linear-gradient(45deg, rgba(30,58,138,0.6), rgba(102,126,234,0.8)); }
        [data-theme="light"] .particle:nth-child(2) { background: linear-gradient(45deg, rgba(30,58,138,0.6), rgba(118,75,162,0.8)); }
        [data-theme="light"] .particle:nth-child(3) { background: linear-gradient(45deg, rgba(102,126,234,0.8), rgba(30,58,138,0.6)); }
        [data-theme="light"] .particle:nth-child(4) { background: linear-gradient(45deg, rgba(118,75,162,0.8), rgba(30,58,138,0.6)); }
        [data-theme="light"] .particle:nth-child(5) { background: linear-gradient(45deg, rgba(30,58,138,0.6), rgba(99,179,237,0.8)); }

        /* Ripple Effects */
        .ripple-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            z-index: 1;
        }

        .ripple {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid rgba(102,126,234,0.3);
            border-radius: 50%;
            animation: rippleExpand 3s ease-out infinite;
        }

        .ripple1 {
            width: 100px;
            height: 100px;
            animation-delay: 0s;
        }

        .ripple2 {
            width: 150px;
            height: 150px;
            animation-delay: 1s;
            opacity: 0.6;
        }

        .ripple3 {
            width: 200px;
            height: 200px;
            animation-delay: 2s;
            opacity: 0.4;
        }

        @keyframes rippleExpand {
            0% { transform: translate(-50%, -50%) scale(0); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
        }

        /* Floating Shapes */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shape {
            position: absolute;
            background: linear-gradient(45deg, rgba(102,126,234,0.2), rgba(118,75,162,0.2));
            border-radius: 50%;
            animation: shapeFloat 8s ease-in-out infinite;
        }

        .shape1 {
            width: 20px;
            height: 20px;
            top: 20%;
            left: 20%;
            animation-delay: 0s;
        }

        .shape2 {
            width: 15px;
            height: 15px;
            top: 70%;
            right: 25%;
            animation-delay: 2s;
        }

        .shape3 {
            width: 25px;
            height: 25px;
            bottom: 30%;
            left: 15%;
            animation-delay: 4s;
        }

        .shape4 {
            width: 18px;
            height: 18px;
            top: 40%;
            right: 15%;
            animation-delay: 6s;
        }

        @keyframes shapeFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            25% { transform: translateY(-20px) rotate(90deg); opacity: 0.6; }
            50% { transform: translateY(-10px) rotate(180deg); opacity: 0.4; }
            75% { transform: translateY(-30px) rotate(270deg); opacity: 0.7; }
        }

        /* Logo Wrapper */
        .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 30px;
        }

        .logo-ring {
            position: absolute;
            top: -15px;
            left: -15px;
            right: -15px;
            bottom: -15px;
            border: 3px solid rgba(102,126,234,0.3);
            border-radius: 50%;
            animation: ringPulse 2s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes ringPulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .logo-glow {
            position: absolute;
            top: -25px;
            left: -25px;
            right: -25px;
            bottom: -25px;
            background: radial-gradient(circle, rgba(102,126,234,0.4) 0%, rgba(118,75,162,0.3) 40%, transparent 70%);
            border-radius: 50%;
            animation: logoGlowPulse 3s ease-in-out infinite;
            z-index: -1;
            filter: blur(8px);
        }

        @keyframes logoGlowPulse {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
            25% { transform: scale(1.1) rotate(90deg); opacity: 0.5; }
            50% { transform: scale(1.2) rotate(180deg); opacity: 0.4; }
            75% { transform: scale(1.1) rotate(270deg); opacity: 0.6; }
        }

        /* Text Content */
        .text-content {
            text-align: center;
            margin-bottom: 30px;
        }

        /* Circular Progress */
        .circular-progress {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }

        .progress-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(from 0deg, rgba(102,126,234,0.8) 0deg, rgba(102,126,234,0.2) 360deg);
            mask: radial-gradient(circle at center, transparent 45%, black 46%);
            -webkit-mask: radial-gradient(circle at center, transparent 45%, black 46%);
            animation: circleRotate 2s linear infinite;
        }

        .progress-fill {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(from 0deg, rgba(102,126,234,0.8) 0deg, transparent 0deg);
            mask: radial-gradient(circle at center, transparent 45%, black 46%);
            -webkit-mask: radial-gradient(circle at center, transparent 45%, black 46%);
            transition: all 0.1s ease;
        }

        @keyframes circleRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        /* Pulse Indicators */
        .pulse-indicators {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(102,126,234,0.8);
            animation: dotPulse 1.5s ease-in-out infinite;
        }

        .pulse-dot:nth-child(2) {
            animation-delay: 0.5s;
        }

        .pulse-dot:nth-child(3) {
            animation-delay: 1s;
        }

        @keyframes dotPulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.5); opacity: 1; }
        }

        /* Light theme adjustments */
        [data-theme="light"] .ripple {
            border-color: rgba(30,64,175,0.3);
        }

        [data-theme="light"] .shape {
            background: linear-gradient(45deg, rgba(30,64,175,0.2), rgba(30,58,138,0.2));
        }

        [data-theme="light"] .logo-ring {
            border-color: rgba(30,64,175,0.3);
        }

        [data-theme="light"] .progress-circle,
        [data-theme="light"] .progress-fill {
            background: conic-gradient(from 0deg, rgba(30,64,175,0.8) 0deg, rgba(30,64,175,0.2) 360deg);
        }

        [data-theme="light"] .pulse-dot {
            background: rgba(30,64,175,0.8);
        }
        .wave-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .wave {
            position: absolute;
            width: 200%;
            height: 200%;
            opacity: 0.1;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            border-radius: 40% 60% 70% 30% / 40% 40% 60% 50%;
            animation: waveFlow 8s ease-in-out infinite;
        }

        .wave1 {
            top: -50%;
            left: -50%;
            animation-delay: 0s;
        }

        .wave2 {
            top: -30%;
            left: -30%;
            animation-delay: 2s;
            opacity: 0.08;
        }

        .wave3 {
            top: -40%;
            left: -40%;
            animation-delay: 4s;
            opacity: 0.06;
        }

        @keyframes waveFlow {
            0%, 100% { transform: translate(0, 0) rotate(0deg); border-radius: 40% 60% 70% 30% / 40% 40% 60% 50%; }
            25% { transform: translate(10px, -10px) rotate(90deg); border-radius: 30% 70% 60% 40% / 50% 60% 40% 50%; }
            50% { transform: translate(-10px, 10px) rotate(180deg); border-radius: 50% 50% 80% 20% / 30% 70% 50% 50%; }
            75% { transform: translate(5px, -5px) rotate(270deg); border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 30px;
        }

        .logo-glow {
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: radial-gradient(circle, rgba(102,126,234,0.3) 0%, rgba(118,75,162,0.2) 50%, transparent 70%);
            border-radius: 50%;
            animation: logoGlow 2s ease-in-out infinite alternate;
            z-index: -1;
        }

        @keyframes logoGlow {
            0% { transform: scale(1) rotate(0deg); opacity: 0.3; filter: blur(5px); }
            25% { transform: scale(1.05) rotate(90deg); opacity: 0.5; filter: blur(8px); }
            50% { transform: scale(1.1) rotate(180deg); opacity: 0.6; filter: blur(10px); }
            75% { transform: scale(1.05) rotate(270deg); opacity: 0.5; filter: blur(8px); }
            100% { transform: scale(1) rotate(360deg); opacity: 0.3; filter: blur(5px); }
        }

        .loading-text-container {
            position: relative;
            margin-bottom: 20px;
        }

        .text-underline {
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            animation: underlineExpand 2s ease-out 1.5s forwards;
        }

        @keyframes underlineExpand {
            0% { width: 0; }
            100% { width: 60%; }
        }

        .modern-progress {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 30px 0;
            gap: 20px;
        }

        .progress-track {
            width: 300px;
            height: 6px;
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
            width: 0%;
            transition: width 0.1s ease;
            position: relative;
        }

        .progress-shine {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: progressShine 2s linear infinite;
        }

        @keyframes progressShine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .progress-percentage {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            min-width: 50px;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .geometric-loader {
            margin-top: 30px;
            perspective: 1000px;
        }

        .cube {
            width: 40px;
            height: 40px;
            position: relative;
            transform-style: preserve-3d;
            animation: cubeRotate 2s linear infinite;
            margin: 0 auto;
        }

        .face {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
        }

        .front { transform: translateZ(20px); }
        .back { transform: rotateY(180deg) translateZ(20px); }
        .right { transform: rotateY(90deg) translateZ(20px); }
        .left { transform: rotateY(-90deg) translateZ(20px); }
        .top { transform: rotateX(90deg) translateZ(20px); }
        .bottom { transform: rotateX(-90deg) translateZ(20px); }

        @keyframes cubeRotate {
            0% { transform: rotateX(0) rotateY(0); }
            100% { transform: rotateX(360deg) rotateY(360deg); }
        }

        /* Light theme adjustments for new loading screen */
        [data-theme="light"] .wave {
            background: linear-gradient(45deg, rgba(30,58,138,0.1), rgba(30,58,138,0.05));
        }

        [data-theme="light"] .logo-glow {
            background: radial-gradient(circle, rgba(30,64,175,0.3) 0%, rgba(30,58,138,0.2) 50%, transparent 70%);
        }

        [data-theme="light"] .progress-track {
            background: rgba(30,58,138,0.2);
        }

        [data-theme="light"] .face {
            border-color: rgba(30,58,138,0.3);
            background: rgba(30,58,138,0.1);
        }
    </style>
</head>
<body data-theme="light">

<!-- Loading Screen -->
<div class="loading-screen" id="loadingScreen">
    <!-- Loading Screen Theme Toggle -->
    <button class="loading-theme-toggle" id="loadingThemeToggle" title="Toggle Theme">
        <i class="fas fa-moon" id="loadingThemeIcon"></i>
    </button>

    <!-- Ripple Effects -->
    <div class="ripple-container">
        <div class="ripple ripple1"></div>
        <div class="ripple ripple2"></div>
        <div class="ripple ripple3"></div>
    </div>

    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
        <div class="shape shape4"></div>
    </div>

    <div class="loading-content">
        <div class="logo-wrapper">
            <img src="image/citylogo.png" alt="City Logo" class="loading-logo">
            <div class="logo-ring"></div>
            <div class="logo-glow"></div>
        </div>

        <div class="text-content">
            <h1 class="loading-text"></h1>
            <p class="loading-subtitle"></p>
        </div>

        <!-- Simple Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <!-- Loading Dots -->
        <div class="loading-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</div>

<!-- Animated Background -->
<div class="background-animation">
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
</div>

<?php
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    echo "<script>alert('You have successfully logged out.');</script>";
}
?>

<div class="login-container">
    <!-- Left Side - Branding -->
    <div class="login-branding">
        <div class="brand-logo">
            <img src="assets/logo.svg" alt="PPMP Logo" style="width: 80px; height: 80px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">
        </div>
        <h1 class="brand-title">PPMP System</h1>
        <p class="brand-subtitle">Procurement Management Platform</p>

        <div class="brand-features">
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Consolidated Item Management</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Real-time Procurement Planning</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Advanced Reporting & Analytics</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Secure User Authentication</span>
            </div>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="login-form-container">
        <!-- Theme Toggle -->
        <button class="theme-toggle-btn" id="themeToggle" title="Toggle Theme">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>

        <div class="form-header">
            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Sign in to your PPMP account</p>
        </div>

        <!-- Error Message -->
        <div class="error-message" id="error-message" style="display: none;"></div>

        <!-- Login Form -->
        <form id="loginForm">
            <div class="form-group username-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
            </div>

            <div class="form-group password-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                <button type="button" class="password-toggle" data-target="password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="form-links">
                <a href="#" onclick="forgotPassword()">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-login" id="loginBtn">
                <span id="btnText">Sign In</span>
                <span class="loading" id="btnLoading" style="display: none;"></span>
            </button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Create Account</a>
        </div>
    </div>
</div>


<!-- Scripts -->
<script src="argondashboard/assets/js/core/bootstrap.bundle.min.js"></script>
<script src="../api_config.js.php"></script>

<script>
// Theme Management for Login Page
class LoginThemeManager {
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
    this.setupLoadingToggle();
    this.updateToggleIcon();
    this.updateLoadingToggleIcon();
  }

  setupToggle() {
    const toggleBtn = document.getElementById('themeToggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        this.toggleTheme();
      });
    }
  }

  setupLoadingToggle() {
    const loadingToggleBtn = document.getElementById('loadingThemeToggle');
    if (loadingToggleBtn) {
      loadingToggleBtn.addEventListener('click', () => {
        this.toggleTheme();
      });
    }
  }

  toggleTheme() {
    this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
    this.applyTheme(this.currentTheme);
    this.saveTheme();
    this.updateToggleIcon();
    this.updateLoadingToggleIcon();
  }

  applyTheme(theme) {
    document.body.setAttribute('data-theme', theme);

    // Update CSS variables
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

  updateLoadingToggleIcon() {
    const icon = document.getElementById('loadingThemeIcon');
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

// Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
  // Initialize loading animation
  initializeLoadingAnimation();

  // Initialize theme manager
  new LoginThemeManager();

  // Setup form handlers
  const loginForm = document.getElementById('loginForm');

  if (loginForm) {
    loginForm.addEventListener('submit', handleLogin);
  }

  // Focus on username field
  const usernameField = document.getElementById('username');
  if (usernameField) {
    usernameField.focus();
  }

  // Setup password toggles
  setupPasswordToggles();
});

function initializeLoadingAnimation() {
  const loadingScreen = document.getElementById('loadingScreen');
  const progressBar = document.getElementById('progressBar');
  const loginContainer = document.querySelector('.login-container');
  const welcomeText = document.querySelector('.loading-text');
  const subtitleText = document.querySelector('.loading-subtitle');

  let progress = 0;
  const duration = 3000; // 3 seconds
  const interval = 50; // Update every 50ms
  const increment = (100 * interval) / duration;

  // Start typing animation for welcome text
  typeWriter(welcomeText, "Welcome to", 100);

  // Start typing animation for subtitle after welcome text
  setTimeout(() => {
    typeWriter(subtitleText, "PPMP System", 150);
  }, 1200);

  const progressInterval = setInterval(() => {
    progress += increment;
    if (progressBar) {
      progressBar.style.width = Math.min(progress, 100) + '%';
    }

    if (progress >= 100) {
      clearInterval(progressInterval);
      // Fade out loading screen
      setTimeout(() => {
        if (loadingScreen) {
          loadingScreen.classList.add('fade-out');
          setTimeout(() => {
            loadingScreen.style.display = 'none';
            // Fade in login container
            if (loginContainer) {
              loginContainer.classList.add('fade-in');
            }
          }, 800);
        }
      }, 300);
    }
  }, interval);
}

function typeWriter(element, text, speed) {
  if (!element) return;

  element.textContent = '';
  let i = 0;

  function type() {
    if (i < text.length) {
      element.textContent += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }

  type();
}

async function handleLogin(event) {
  event.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();
  const errorMessage = document.getElementById('error-message');
  const loginBtn = document.getElementById('loginBtn');
  const btnText = document.getElementById('btnText');
  const btnLoading = document.getElementById('btnLoading');

  // Clear previous error
  errorMessage.style.display = 'none';

  // Validate inputs
  if (!username || !password) {
    showError('Please fill in all fields.');
    return;
  }

  // Show loading state
  loginBtn.disabled = true;
  btnText.style.display = 'none';
  btnLoading.style.display = 'inline-block';

  try {
    const response = await fetch(`${API_BASE_URL}/api_auth.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ username, password })
    });

    const data = await response.json();

    if (data.status === 'success') {
      // Success - redirect to dashboard
      window.location.href = 'dashboard.php';
    } else {
      // Error - show message
      showError(data.message || 'Login failed. Please try again.');
    }
  } catch (error) {
    console.error('Login error:', error);
    showError('Network error. Please check your connection and try again.');
  } finally {
    // Reset loading state
    loginBtn.disabled = false;
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

function forgotPassword() {
  alert('Please contact your administrator to reset your password.');
}
</script>

</body>
</html>
