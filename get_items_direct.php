<?php
// Set default REQUEST_METHOD if not set (for CLI testing)
if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

// Increase timeouts and memory for large data handling
set_time_limit(120);
ini_set('memory_limit', '512M');
ini_set('mysql.connect_timeout', 30);
ini_set('default_socket_timeout', 60);

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

    error_log("DEBUG: Attempting database connection to host=$host, dbname=$dbname, user=$username");

    // Add connection timeout and options
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5, // 5 second connection timeout
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

    error_log("DEBUG: Database connection successful");

    // Check if this is a DataTables request
    $isDataTables = isset($_GET['draw']);

    if ($isDataTables) {
        // Handle DataTables server-side processing
        $draw = (int)$_GET['draw'];
        $start = (int)$_GET['start'];
        $length = (int)$_GET['length'];
        $searchValue = $_GET['search']['value'] ?? '';

        // Column ordering
        $orderColumn = $_GET['order'][0]['column'] ?? 1; // Default to Item_Code
        $orderDir = $_GET['order'][0]['dir'] ?? 'asc';

        // Map column index to database column
        $columns = ['ID', 'Item_Code', 'Item_Name', 'Items_Description', 'Unit', 'Unit_Cost', 'Category'];
        $orderBy = $columns[$orderColumn] ?? 'Item_Code';

        // Build WHERE clause for search
        $whereClause = '';
        $params = [];
        $conditions = [];

        // Global search
        if (!empty($searchValue)) {
            $conditions[] = "(Item_Code LIKE :search OR Item_Name LIKE :search OR Items_Description LIKE :search OR Category LIKE :search)";
            $params[':search'] = '%' . $searchValue . '%';
        }

        // Custom filters
        if (!empty($_GET['item_code'])) {
            $conditions[] = "Item_Code LIKE :item_code";
            $params[':item_code'] = '%' . $_GET['item_code'] . '%';
        }
        if (!empty($_GET['item_name'])) {
            $conditions[] = "Item_Name LIKE :item_name";
            $params[':item_name'] = '%' . $_GET['item_name'] . '%';
        }
        if (!empty($_GET['category'])) {
            $conditions[] = "Category LIKE :category";
            $params[':category'] = '%' . $_GET['category'] . '%';
        }

        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }

        // Get total count
        $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_ppmp_bac_items $whereClause");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get filtered count (same as total if no search)
        $filteredCount = $totalCount;

        // Get data with pagination and ordering
        $stmt = $conn->prepare("SELECT ID, Item_Code, Item_Name, Items_Description, Unit, Unit_Cost, Category FROM tbl_ppmp_bac_items $whereClause ORDER BY $orderBy $orderDir LIMIT $length OFFSET $start");
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format data for DataTables
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                $item['ID'], // Hidden ID for actions
                $item['Item_Code'],
                $item['Item_Name'] ?: 'N/A',
                $item['Items_Description'],
                $item['Unit'],
                '₱' . number_format($item['Unit_Cost'], 2),
                $item['Category'] ?: 'N/A',
                '' // Actions column, will be filled by JS
            ];
        }

        $response = json_encode([
            'draw' => $draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo $response;
        exit;
    }

    // Legacy pagination for backward compatibility
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    // Ensure reasonable limits
    $limit = min(max($limit, 10), 200);
    $offset = max($offset, 0);

    // Get total count for pagination
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_ppmp_bac_items");
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Direct database query without authentication with pagination
    $stmt = $conn->prepare("SELECT ID, Item_Code, Item_Name, Items_Description, Unit, Unit_Cost, Category FROM tbl_ppmp_bac_items ORDER BY Item_Code ASC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Items count: " . count($items) . " (page with limit $limit, offset $offset, total $totalCount)");

    // Test json_encode first
    $testJson = json_encode(['test' => 'value']);
    error_log("Test JSON: " . $testJson);

    // Clean the data to handle UTF-8 issues
    $cleanItems = [];
    foreach ($items as $item) {
        $cleanItem = [];
        foreach ($item as $key => $value) {
            $cleanItem[$key] = is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'UTF-8') : $value;
        }
        $cleanItems[] = $cleanItem;
    }

    $response = json_encode([
        'success' => true,
        'items' => $cleanItems,
        'count' => count($cleanItems),
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $jsonError = json_last_error();
    error_log("JSON encode error: " . $jsonError . " - " . json_last_error_msg());

    error_log("get_items_direct.php response length: " . strlen($response));
    error_log("Response preview: " . substr($response, 0, 200));

    // Debug: Write to file as well
    $debugFile = __DIR__ . '/debug_response.json';
    $writeResult = file_put_contents($debugFile, $response);
    error_log("Wrote to debug file: " . $debugFile . " - bytes written: " . $writeResult);

    echo $response;

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("DEBUG: PDO Error Code: " . $e->getCode());
    error_log("DEBUG: PDO Error Info: " . json_encode($e->errorInfo));
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