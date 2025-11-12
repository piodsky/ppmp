<?php
// Token-based logout - this page clears tokens from database and client-side
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../apiPPMP');
$dotenv->load();

$host     = $_ENV['DB_HOST'];
$dbname   = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once __DIR__ . "/../apiPPMP/token_helper.php";
TokenHelper::init($conn);

// Get token from Authorization header or cookie
$token = null;
if (isset($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
    $token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
} elseif (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}

// If we have a token, delete it from database
if ($token) {
    TokenHelper::deleteToken($token);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out...</title>
    <script>
        // Clear localStorage tokens and cookies, then redirect
        document.addEventListener('DOMContentLoaded', function() {
            // Clear localStorage tokens
            localStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            localStorage.removeItem('token_expires_at');
            localStorage.removeItem('user_data');

            // Clear auth cookie
            document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; secure; samesite=strict';

            // Clear any other session cookies
            document.cookie = 'ppmp_session=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';

            // Clear theme preference
            localStorage.removeItem('ppmp-theme');

            // Redirect to login page
            window.location.href = 'login.php?logout=success';
        });
    </script>
</body>
</html>
