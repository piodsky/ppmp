<?php
// Lightweight token-based logout - optimized for performance
// Load minimal configuration without autoloader
$envFile = __DIR__ . '/../apiPPMP/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!getenv($name)) {
                putenv("$name=$value");
            }
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'ppmp_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// Simple token helper without autoloader
function deleteToken($pdo, $token) {
    try {
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE token = :token");
        $stmt->bindParam(":token", $token);
        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

// Get token from cookie or header
$token = $_COOKIE['auth_token'] ?? null;

// Try to get from Authorization header
if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
    }
}

// Delete token if we have one
if ($token) {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
        ]);
        deleteToken($conn, $token);
    } catch (PDOException $e) {
        // Database connection failed, continue with client-side logout
    }
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
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .logout-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <p>Logging you out...</p>
    </div>
</body>
</html>
