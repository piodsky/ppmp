<?php
// Get distinct categories API
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../apiPPMP');
$dotenv->load();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $host     = $_ENV['DB_HOST'];
    $dbname   = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get distinct categories
    $stmt = $conn->prepare("SELECT DISTINCT Category FROM tbl_ppmp_bac_items WHERE Category IS NOT NULL AND Category != '' ORDER BY Category ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $response = json_encode([
        'success' => true,
        'categories' => $categories,
        'count' => count($categories)
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    echo $response;

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $response = json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
    echo $response;
} catch (Exception $e) {
    error_log("System error: " . $e->getMessage());
    $response = json_encode([
        'success' => false,
        'error' => 'System error: ' . $e->getMessage()
    ]);
    echo $response;
}
?>