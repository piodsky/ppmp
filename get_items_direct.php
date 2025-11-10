<?php
// Set default REQUEST_METHOD if not set (for CLI testing)
if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

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
    require_once __DIR__ . "/../apiPPMP/config.php";

    // Direct database query without authentication
    $stmt = $conn->prepare("SELECT ID, Item_Code, Item_Name, Items_Description, Unit, Unit_Cost, Category FROM tbl_ppmp_bac_items ORDER BY Item_Code ASC");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Items count: " . count($items));

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
        'count' => count($cleanItems)
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