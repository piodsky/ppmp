<?php
session_start();

// Set session variables from POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User data
    if (isset($_POST['id'])) $_SESSION['user_id'] = $_POST['id'];
    if (isset($_POST['username'])) $_SESSION['username'] = $_POST['username'];
    if (isset($_POST['firstname'])) $_SESSION['firstname'] = $_POST['firstname'];
    if (isset($_POST['middlename'])) $_SESSION['middlename'] = $_POST['middlename'];
    if (isset($_POST['lastname'])) $_SESSION['lastname'] = $_POST['lastname'];
    if (isset($_POST['name_ext'])) $_SESSION['name_ext'] = $_POST['name_ext'];
    if (isset($_POST['role'])) $_SESSION['role'] = $_POST['role'];
    if (isset($_POST['profile_picture'])) $_SESSION['profile_picture'] = $_POST['profile_picture'];
    if (isset($_POST['department'])) $_SESSION['department'] = $_POST['department'];

    // Token data
    if (isset($_POST['access_token'])) $_SESSION['access_token'] = $_POST['access_token'];

    // Set logged in flag
    $_SESSION['logged_in'] = true;

    // Redirect to dashboard
    header("Location: dashboard.php");
    exit();
} else {
    // Invalid request method
    header("Location: login.php");
    exit();
}
?>