<?php
// Search Items API - Direct database search without authentication
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
    // Set a reasonable timeout for database operations
    set_time_limit(30); // 30 seconds max execution time

    // Disable default connection timeout and set custom options
    ini_set('mysql.connect_timeout', 10);
    ini_set('default_socket_timeout', 15);

    require_once __DIR__ . "/../apiPPMP/config.php";

    // Test the connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Set PDO attributes for better timeout handling
    $conn->setAttribute(PDO::ATTR_TIMEOUT, 10); // 10 second timeout

    // Test connection with a simple query
    $testStmt = $conn->prepare("SELECT 1");
    $testStmt->execute();
    if (!$testStmt) {
        throw new Exception('Database connection test failed');
    }

    // Get search parameters
    $itemName = isset($_GET['item_name']) ? trim($_GET['item_name']) : '';
    $description = isset($_GET['description']) ? trim($_GET['description']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';

    // Build WHERE clause
    $whereConditions = [];
    $params = [];

    if (!empty($itemName)) {
        $whereConditions[] = "Item_Name LIKE :item_name";
        $params[':item_name'] = '%' . $itemName . '%';
    }

    if (!empty($description)) {
        $whereConditions[] = "Items_Description LIKE :description";
        $params[':description'] = '%' . $description . '%';
    }

    if (!empty($category)) {
        $whereConditions[] = "Category LIKE :category";
        $params[':category'] = '%' . $category . '%';
    }

    // If no search criteria provided, return empty result
    if (empty($whereConditions)) {
        echo json_encode([
            'success' => true,
            'items' => [],
            'count' => 0,
            'message' => 'No search criteria provided'
        ]);
        exit;
    }

    $whereClause = "WHERE " . implode(" AND ", $whereConditions);

    // Execute search query with timeout handling
    $stmt = $conn->prepare("SELECT ID, Item_Code, Item_Name, Items_Description, Unit, Unit_Cost, Category FROM tbl_ppmp_bac_items {$whereClause} ORDER BY Item_Code ASC");

    // Set query timeout
    $stmt->setAttribute(PDO::ATTR_TIMEOUT, 10);

    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Search query executed, found " . count($items) . " items");

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
        'search_criteria' => [
            'item_name' => $itemName,
            'description' => $description,
            'category' => $category
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    error_log("Search response length: " . strlen($response));
    echo $response;

} catch (PDOException $e) {
    error_log("Database search error: " . $e->getMessage());

    // Provide user-friendly error messages
    $errorMessage = 'Database connection error';
    if (strpos($e->getMessage(), 'Connection timed out') !== false) {
        $errorMessage = 'Database connection timed out. Please try again.';
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        $errorMessage = 'Database access denied. Please check credentials.';
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        $errorMessage = 'Database not found. Please check configuration.';
    }

    $response = json_encode([
        'success' => false,
        'error' => $errorMessage,
        'details' => $e->getMessage()
    ]);
    echo $response;
} catch (Exception $e) {
    error_log("System search error: " . $e->getMessage());
    $response = json_encode([
        'success' => false,
        'error' => 'System error occurred. Please try again.',
        'details' => $e->getMessage()
    ]);
    echo $response;
}
?>