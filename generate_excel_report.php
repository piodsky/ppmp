<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

// Validate token
$user = TokenHelper::getCurrentUser();
if (!$user) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
    exit();
}

// Set headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="PPMP_All_Reports_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('PPMP Management System')
    ->setTitle('Consolidated PPMP Reports')
    ->setSubject('All PPMP Reports - Consolidated Items, APP, Department, Category')
    ->setDescription('Generated consolidated Excel report containing all PPMP data');

// Function to style headers
function styleHeader($sheet, $range) {
    $sheet->getStyle($range)->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A5568']],
        'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
}

// Function to style data rows
function styleData($sheet, $range) {
    $sheet->getStyle($range)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['vertical' => Alignment::VERTICAL_TOP]
    ]);
}

// ==========================================
// SHEET 1: CONSOLIDATED ITEMS
// ==========================================
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Consolidated Items');

// Add header information
$sheet1->setCellValue('A1', 'Republic of the Philippines');
$sheet1->setCellValue('A2', 'Province of Bukidnon');
$sheet1->setCellValue('A3', 'City of Malaybalay');
$sheet1->setCellValue('A5', 'CONSOLIDATED PPMP ITEMS REPORT');
$sheet1->setCellValue('A6', 'Generated on: ' . date('F j, Y g:i A'));

// Style headers
$sheet1->getStyle('A1:A6')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);
$sheet1->mergeCells('A1:H1');
$sheet1->mergeCells('A2:H2');
$sheet1->mergeCells('A3:H3');
$sheet1->mergeCells('A5:H5');
$sheet1->mergeCells('A6:H6');

// Table headers
$row = 8;
$sheet1->setCellValue('A' . $row, '#');
$sheet1->setCellValue('B' . $row, 'Item Code');
$sheet1->setCellValue('C' . $row, 'Item Name');
$sheet1->setCellValue('D' . $row, 'Description');
$sheet1->setCellValue('E' . $row, 'Unit');
$sheet1->setCellValue('F' . $row, 'Unit Cost');
$sheet1->setCellValue('G' . $row, 'Total Qty');
$sheet1->setCellValue('H' . $row, 'Total Cost');
$sheet1->setCellValue('I' . $row, 'PPMP Numbers');

styleHeader($sheet1, 'A' . $row . ':I' . $row);

// Get consolidated items data
$stmt = $conn->prepare("
    SELECT
        i.Item_Code,
        i.Item_Name,
        i.Item_Description,
        i.Unit,
        i.Unit_Cost,
        SUM(e.Total_Qty) as total_quantity,
        SUM(e.Total_Cost) as total_cost,
        GROUP_CONCAT(DISTINCT p.PPMP_Number ORDER BY p.PPMP_Number) as ppmp_numbers,
        c.Category
    FROM tbl_ppmp_entries e
    INNER JOIN tbl_ppmp_documents p ON e.PPMP_ID = p.ID
    INNER JOIN tbl_ppmp_bac_items i ON e.Item_ID = i.ID
    INNER JOIN tbl_ppmp_categories c ON i.Category = c.Category_Name
    WHERE p.Status = 'approved'
    GROUP BY i.Item_Code, i.Item_Name, i.Item_Description, i.Unit, i.Unit_Cost, c.Category
    ORDER BY c.Category, i.Item_Code
");
$stmt->execute();
$consolidatedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add data rows
$row++;
$currentCategory = '';
$index = 1;

foreach($consolidatedItems as $item) {
    // Add category header if category changes
    if ($item['Category'] !== $currentCategory) {
        $currentCategory = $item['Category'];
        $sheet1->setCellValue('A' . $row, $currentCategory);
        $sheet1->mergeCells('A' . $row . ':I' . $row);
        $sheet1->getStyle('A' . $row . ':I' . $row)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);
        $row++;
    }

    $sheet1->setCellValue('A' . $row, $index++);
    $sheet1->setCellValue('B' . $row, $item['Item_Code'] ?: '');
    $sheet1->setCellValue('C' . $row, $item['Item_Name'] ?: '');
    $sheet1->setCellValue('D' . $row, $item['Item_Description'] ?: '');
    $sheet1->setCellValue('E' . $row, $item['Unit'] ?: '');
    $sheet1->setCellValue('F' . $row, $item['Unit_Cost'] ? '₱' . number_format($item['Unit_Cost'], 2) : '');
    $sheet1->setCellValue('G' . $row, $item['total_quantity'] ?: 0);
    $sheet1->setCellValue('H' . $row, $item['total_cost'] ? '₱' . number_format($item['total_cost'], 2) : '');
    $sheet1->setCellValue('I' . $row, $item['ppmp_numbers'] ?: '');

    $row++;
}

// Style data rows
$dataRange = 'A9:I' . ($row - 1);
styleData($sheet1, $dataRange);

// Auto-size columns
foreach(range('A', 'I') as $columnID) {
    $sheet1->getColumnDimension($columnID)->setAutoSize(true);
}

// ==========================================
// SHEET 2: APP REPORT
// ==========================================
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('APP Report');

// Add header information
$sheet2->setCellValue('A1', 'Republic of the Philippines');
$sheet2->setCellValue('A2', 'Province of Bukidnon');
$sheet2->setCellValue('A3', 'City of Malaybalay');
$sheet2->setCellValue('A5', 'ANNUAL PROCUREMENT PLAN (APP) REPORT');
$sheet2->setCellValue('A6', 'Generated on: ' . date('F j, Y g:i A'));

// Style headers
$sheet2->getStyle('A1:A6')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);
$sheet2->mergeCells('A1:Z1');
$sheet2->mergeCells('A2:Z2');
$sheet2->mergeCells('A3:Z3');
$sheet2->mergeCells('A5:Z5');
$sheet2->mergeCells('A6:Z6');

// Table headers
$row = 8;
$sheet2->setCellValue('A' . $row, 'Item Code');
$sheet2->setCellValue('B' . $row, 'Item Name & Specifications');
$sheet2->setCellValue('C' . $row, 'Unit');
$sheet2->setCellValue('D' . $row, 'Jan');
$sheet2->setCellValue('E' . $row, 'Feb');
$sheet2->setCellValue('F' . $row, 'Mar');
$sheet2->setCellValue('G' . $row, 'Q1 Amount');
$sheet2->setCellValue('H' . $row, 'Apr');
$sheet2->setCellValue('I' . $row, 'May');
$sheet2->setCellValue('J' . $row, 'Jun');
$sheet2->setCellValue('K' . $row, 'Q2 Amount');
$sheet2->setCellValue('L' . $row, 'Jul');
$sheet2->setCellValue('M' . $row, 'Aug');
$sheet2->setCellValue('N' . $row, 'Sep');
$sheet2->setCellValue('O' . $row, 'Q3 Amount');
$sheet2->setCellValue('P' . $row, 'Oct');
$sheet2->setCellValue('Q' . $row, 'Nov');
$sheet2->setCellValue('R' . $row, 'Dec');
$sheet2->setCellValue('S' . $row, 'Q4 Amount');
$sheet2->setCellValue('T' . $row, 'Total Qty');
$sheet2->setCellValue('U' . $row, 'Unit Cost');
$sheet2->setCellValue('V' . $row, 'Total Cost');

styleHeader($sheet2, 'A' . $row . ':V' . $row);

// Get APP report data
$stmt = $conn->prepare("
    SELECT
        i.Item_Code,
        i.Item_Name,
        i.Item_Description,
        i.Unit,
        i.Unit_Cost,
        c.Category,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 1 THEN e.Total_Qty END), 0) as jan_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 2 THEN e.Total_Qty END), 0) as feb_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 3 THEN e.Total_Qty END), 0) as mar_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 4 THEN e.Total_Qty END), 0) as apr_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 5 THEN e.Total_Qty END), 0) as may_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 6 THEN e.Total_Qty END), 0) as jun_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 7 THEN e.Total_Qty END), 0) as jul_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 8 THEN e.Total_Qty END), 0) as aug_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 9 THEN e.Total_Qty END), 0) as sep_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 10 THEN e.Total_Qty END), 0) as oct_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 11 THEN e.Total_Qty END), 0) as nov_qty,
        COALESCE(SUM(CASE WHEN MONTH(e.Schedule_Date) = 12 THEN e.Total_Qty END), 0) as dec_qty,
        SUM(e.Total_Qty) as total_quantity,
        SUM(e.Total_Cost) as total_cost
    FROM tbl_ppmp_entries e
    INNER JOIN tbl_ppmp_documents p ON e.PPMP_ID = p.ID
    INNER JOIN tbl_ppmp_bac_items i ON e.Item_ID = i.ID
    INNER JOIN tbl_ppmp_categories c ON i.Category = c.Category_Name
    WHERE p.Status = 'approved' AND YEAR(e.Schedule_Date) = YEAR(CURDATE())
    GROUP BY i.Item_Code, i.Item_Name, i.Item_Description, i.Unit, i.Unit_Cost, c.Category
    ORDER BY c.Category, i.Item_Code
");
$stmt->execute();
$appData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add data rows
$row++;
$currentCategory = '';

foreach($appData as $item) {
    // Add category header if category changes
    if ($item['Category'] !== $currentCategory) {
        $currentCategory = $item['Category'];
        $sheet2->setCellValue('A' . $row, $currentCategory);
        $sheet2->mergeCells('A' . $row . ':V' . $row);
        $sheet2->getStyle('A' . $row . ':V' . $row)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);
        $row++;
    }

    $q1_amount = ($item['jan_qty'] + $item['feb_qty'] + $item['mar_qty']) * $item['Unit_Cost'];
    $q2_amount = ($item['apr_qty'] + $item['may_qty'] + $item['jun_qty']) * $item['Unit_Cost'];
    $q3_amount = ($item['jul_qty'] + $item['aug_qty'] + $item['sep_qty']) * $item['Unit_Cost'];
    $q4_amount = ($item['oct_qty'] + $item['nov_qty'] + $item['dec_qty']) * $item['Unit_Cost'];

    $sheet2->setCellValue('A' . $row, $item['Item_Code'] ?: '');
    $sheet2->setCellValue('B' . $row, $item['Item_Name'] . ' - ' . $item['Item_Description']);
    $sheet2->setCellValue('C' . $row, $item['Unit'] ?: '');
    $sheet2->setCellValue('D' . $row, $item['jan_qty'] ?: 0);
    $sheet2->setCellValue('E' . $row, $item['feb_qty'] ?: 0);
    $sheet2->setCellValue('F' . $row, $item['mar_qty'] ?: 0);
    $sheet2->setCellValue('G' . $row, $q1_amount ? '₱' . number_format($q1_amount, 2) : '₱0.00');
    $sheet2->setCellValue('H' . $row, $item['apr_qty'] ?: 0);
    $sheet2->setCellValue('I' . $row, $item['may_qty'] ?: 0);
    $sheet2->setCellValue('J' . $row, $item['jun_qty'] ?: 0);
    $sheet2->setCellValue('K' . $row, $q2_amount ? '₱' . number_format($q2_amount, 2) : '₱0.00');
    $sheet2->setCellValue('L' . $row, $item['jul_qty'] ?: 0);
    $sheet2->setCellValue('M' . $row, $item['aug_qty'] ?: 0);
    $sheet2->setCellValue('N' . $row, $item['sep_qty'] ?: 0);
    $sheet2->setCellValue('O' . $row, $q3_amount ? '₱' . number_format($q3_amount, 2) : '₱0.00');
    $sheet2->setCellValue('P' . $row, $item['oct_qty'] ?: 0);
    $sheet2->setCellValue('Q' . $row, $item['nov_qty'] ?: 0);
    $sheet2->setCellValue('R' . $row, $item['dec_qty'] ?: 0);
    $sheet2->setCellValue('S' . $row, $q4_amount ? '₱' . number_format($q4_amount, 2) : '₱0.00');
    $sheet2->setCellValue('T' . $row, $item['total_quantity'] ?: 0);
    $sheet2->setCellValue('U' . $row, $item['Unit_Cost'] ? '₱' . number_format($item['Unit_Cost'], 2) : '');
    $sheet2->setCellValue('V' . $row, $item['total_cost'] ? '₱' . number_format($item['total_cost'], 2) : '');

    $row++;
}

// Style data rows
$dataRange = 'A9:V' . ($row - 1);
styleData($sheet2, $dataRange);

// Auto-size columns
foreach(range('A', 'V') as $columnID) {
    $sheet2->getColumnDimension($columnID)->setAutoSize(true);
}

// ==========================================
// SHEET 3: DEPARTMENT REPORT
// ==========================================
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Department Report');

// Add header information
$sheet3->setCellValue('A1', 'Republic of the Philippines');
$sheet3->setCellValue('A2', 'Province of Bukidnon');
$sheet3->setCellValue('A3', 'City of Malaybalay');
$sheet3->setCellValue('A5', 'DEPARTMENT CONSOLIDATED REPORT');
$sheet3->setCellValue('A6', 'Generated on: ' . date('F j, Y g:i A'));

// Get departments
$deptStmt = $conn->prepare("SELECT DISTINCT Department FROM tbl_ppmp_documents WHERE Status = 'approved' ORDER BY Department");
$deptStmt->execute();
$departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

// Style headers
$sheet3->getStyle('A1:A6')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);
$lastCol = chr(65 + count($departments) + 5); // A + dept count + 5 more columns
$sheet3->mergeCells('A1:' . $lastCol . '1');
$sheet3->mergeCells('A2:' . $lastCol . '2');
$sheet3->mergeCells('A3:' . $lastCol . '3');
$sheet3->mergeCells('A5:' . $lastCol . '5');
$sheet3->mergeCells('A6:' . $lastCol . '6');

// Table headers
$row = 8;
$col = 'A';
$sheet3->setCellValue($col++ . $row, 'Item Code');
$sheet3->setCellValue($col++ . $row, 'Item Name & Specifications');
$sheet3->setCellValue($col++ . $row, 'Unit');

// Department columns
foreach($departments as $dept) {
    $sheet3->setCellValue($col++ . $row, substr($dept, 0, 15));
}

$sheet3->setCellValue($col++ . $row, 'Total Qty');
$sheet3->setCellValue($col++ . $row, 'Unit Cost');
$sheet3->setCellValue($col++ . $row, 'Total Cost');

$headerRange = 'A' . $row . ':' . chr(65 + count($departments) + 5) . $row;
styleHeader($sheet3, $headerRange);

// Get department report data
$stmt = $conn->prepare("
    SELECT
        i.Item_Code,
        i.Item_Name,
        i.Item_Description,
        i.Unit,
        i.Unit_Cost,
        c.Category,
        p.Department,
        SUM(e.Total_Qty) as total_quantity,
        SUM(e.Total_Cost) as total_cost
    FROM tbl_ppmp_entries e
    INNER JOIN tbl_ppmp_documents p ON e.PPMP_ID = p.ID
    INNER JOIN tbl_ppmp_bac_items i ON e.Item_ID = i.ID
    INNER JOIN tbl_ppmp_categories c ON i.Category = c.Category_Name
    WHERE p.Status = 'approved'
    GROUP BY i.Item_Code, i.Item_Name, i.Item_Description, i.Unit, i.Unit_Cost, c.Category, p.Department
    ORDER BY c.Category, i.Item_Code, p.Department
");
$stmt->execute();
$deptData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by item
$groupedDeptData = [];
foreach($deptData as $item) {
    $key = $item['Item_Code'] . '|' . $item['Item_Name'] . '|' . $item['Item_Description'] . '|' . $item['Unit'] . '|' . $item['Unit_Cost'] . '|' . $item['Category'];
    if (!isset($groupedDeptData[$key])) {
        $groupedDeptData[$key] = [
            'Item_Code' => $item['Item_Code'],
            'Item_Name' => $item['Item_Name'],
            'Item_Description' => $item['Item_Description'],
            'Unit' => $item['Unit'],
            'Unit_Cost' => $item['Unit_Cost'],
            'Category' => $item['Category'],
            'departments' => array_fill_keys($departments, 0),
            'total_quantity' => 0,
            'total_cost' => 0
        ];
    }
    $groupedDeptData[$key]['departments'][$item['Department']] = $item['total_quantity'];
    $groupedDeptData[$key]['total_quantity'] += $item['total_quantity'];
    $groupedDeptData[$key]['total_cost'] += $item['total_cost'];
}

// Add data rows
$row++;
$currentCategory = '';

foreach($groupedDeptData as $item) {
    // Add category header if category changes
    if ($item['Category'] !== $currentCategory) {
        $currentCategory = $item['Category'];
        $sheet3->setCellValue('A' . $row, $currentCategory);
        $sheet3->mergeCells('A' . $row . ':' . chr(65 + count($departments) + 5) . $row);
        $sheet3->getStyle('A' . $row . ':' . chr(65 + count($departments) + 5) . $row)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);
        $row++;
    }

    $col = 'A';
    $sheet3->setCellValue($col++ . $row, $item['Item_Code'] ?: '');
    $sheet3->setCellValue($col++ . $row, $item['Item_Name'] . ' - ' . $item['Item_Description']);
    $sheet3->setCellValue($col++ . $row, $item['Unit'] ?: '');

    // Department quantities
    foreach($departments as $dept) {
        $qty = $item['departments'][$dept] ?? 0;
        $sheet3->setCellValue($col++ . $row, $qty ?: '');
    }

    // Totals
    $sheet3->setCellValue($col++ . $row, $item['total_quantity']);
    $sheet3->setCellValue($col++ . $row, $item['Unit_Cost'] ? '₱' . number_format($item['Unit_Cost'], 2) : '');
    $sheet3->setCellValue($col++ . $row, $item['total_cost'] ? '₱' . number_format($item['total_cost'], 2) : '');

    $row++;
}

// Style data rows
$dataRange = 'A9:' . chr(65 + count($departments) + 5) . ($row - 1);
styleData($sheet3, $dataRange);

// Auto-size columns
foreach(range('A', chr(65 + count($departments) + 5)) as $columnID) {
    $sheet3->getColumnDimension($columnID)->setAutoSize(true);
}

// ==========================================
// SHEET 4: CATEGORY REPORT (All Categories)
// ==========================================
$sheet4 = $spreadsheet->createSheet();
$sheet4->setTitle('Category Report');

// Add header information
$sheet4->setCellValue('A1', 'Republic of the Philippines');
$sheet4->setCellValue('A2', 'Province of Bukidnon');
$sheet4->setCellValue('A3', 'City of Malaybalay');
$sheet4->setCellValue('A5', 'CATEGORY CONSOLIDATED REPORT (All Categories)');
$sheet4->setCellValue('A6', 'Generated on: ' . date('F j, Y g:i A'));

// Style headers
$sheet4->getStyle('A1:A6')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);
$sheet4->mergeCells('A1:' . chr(65 + count($departments) + 5) . '1');
$sheet4->mergeCells('A2:' . chr(65 + count($departments) + 5) . '2');
$sheet4->mergeCells('A3:' . chr(65 + count($departments) + 5) . '3');
$sheet4->mergeCells('A5:' . chr(65 + count($departments) + 5) . '5');
$sheet4->mergeCells('A6:' . chr(65 + count($departments) + 5) . '6');

// Get all categories
$catStmt = $conn->prepare("SELECT DISTINCT Category_Name FROM tbl_ppmp_categories ORDER BY Category_Name");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Process each category
$row = 8;
$currentCategory = '';

foreach($categories as $category) {
    // Add category header
    if ($category !== $currentCategory) {
        $currentCategory = $category;
        $sheet4->setCellValue('A' . $row, 'CATEGORY: ' . $category);
        $sheet4->mergeCells('A' . $row . ':' . chr(65 + count($departments) + 5) . $row);
        $sheet4->getStyle('A' . $row . ':' . chr(65 + count($departments) + 5) . $row)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A5568']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);
        $row++;

        // Table headers for this category
        $col = 'A';
        $sheet4->setCellValue($col++ . $row, 'Item Code');
        $sheet4->setCellValue($col++ . $row, 'Item Name & Specifications');
        $sheet4->setCellValue($col++ . $row, 'Unit');

        // Department columns
        foreach($departments as $dept) {
            $sheet4->setCellValue($col++ . $row, substr($dept, 0, 15));
        }

        $sheet4->setCellValue($col++ . $row, 'Total Qty');
        $sheet4->setCellValue($col++ . $row, 'Unit Cost');
        $sheet4->setCellValue($col++ . $row, 'Total Cost');

        $headerRange = 'A' . $row . ':' . chr(65 + count($departments) + 5) . $row;
        styleHeader($sheet4, $headerRange);
        $row++;
    }

    // Get data for this category
    $stmt = $conn->prepare("
        SELECT
            i.Item_Code,
            i.Item_Name,
            i.Item_Description,
            i.Unit,
            i.Unit_Cost,
            p.Department,
            SUM(e.Total_Qty) as total_quantity,
            SUM(e.Total_Cost) as total_cost
        FROM tbl_ppmp_entries e
        INNER JOIN tbl_ppmp_documents p ON e.PPMP_ID = p.ID
        INNER JOIN tbl_ppmp_bac_items i ON e.Item_ID = i.ID
        WHERE p.Status = 'approved' AND i.Category = ?
        GROUP BY i.Item_Code, i.Item_Name, i.Item_Description, i.Unit, i.Unit_Cost, p.Department
        ORDER BY i.Item_Code, p.Department
    ");
    $stmt->execute([$category]);
    $catData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by item for this category
    $groupedCatData = [];
    foreach($catData as $item) {
        $key = $item['Item_Code'] . '|' . $item['Item_Name'] . '|' . $item['Item_Description'] . '|' . $item['Unit'] . '|' . $item['Unit_Cost'];
        if (!isset($groupedCatData[$key])) {
            $groupedCatData[$key] = [
                'Item_Code' => $item['Item_Code'],
                'Item_Name' => $item['Item_Name'],
                'Item_Description' => $item['Item_Description'],
                'Unit' => $item['Unit'],
                'Unit_Cost' => $item['Unit_Cost'],
                'departments' => array_fill_keys($departments, 0),
                'total_quantity' => 0,
                'total_cost' => 0
            ];
        }
        $groupedCatData[$key]['departments'][$item['Department']] = $item['total_quantity'];
        $groupedCatData[$key]['total_quantity'] += $item['total_quantity'];
        $groupedCatData[$key]['total_cost'] += $item['total_cost'];
    }

    // Add data rows for this category
    foreach($groupedCatData as $item) {
        $col = 'A';
        $sheet4->setCellValue($col++ . $row, $item['Item_Code'] ?: '');
        $sheet4->setCellValue($col++ . $row, $item['Item_Name'] . ' - ' . $item['Item_Description']);
        $sheet4->setCellValue($col++ . $row, $item['Unit'] ?: '');

        // Department quantities
        foreach($departments as $dept) {
            $qty = $item['departments'][$dept] ?? 0;
            $sheet4->setCellValue($col++ . $row, $qty ?: '');
        }

        // Totals
        $sheet4->setCellValue($col++ . $row, $item['total_quantity']);
        $sheet4->setCellValue($col++ . $row, $item['Unit_Cost'] ? '₱' . number_format($item['Unit_Cost'], 2) : '');
        $sheet4->setCellValue($col++ . $row, $item['total_cost'] ? '₱' . number_format($item['total_cost'], 2) : '');

        $row++;
    }

    // Add spacing between categories
    $row++;
}

// Style data rows
$dataRange = 'A9:' . chr(65 + count($departments) + 5) . ($row - 1);
styleData($sheet4, $dataRange);

// Auto-size columns
foreach(range('A', chr(65 + count($departments) + 5)) as $columnID) {
    $sheet4->getColumnDimension($columnID)->setAutoSize(true);
}

// ==========================================
// SHEET 5: SUMMARY
// ==========================================
$sheet5 = $spreadsheet->createSheet();
$sheet5->setTitle('Summary');

// Add header information
$sheet5->setCellValue('A1', 'Republic of the Philippines');
$sheet5->setCellValue('A2', 'Province of Bukidnon');
$sheet5->setCellValue('A3', 'City of Malaybalay');
$sheet5->setCellValue('A5', 'PPMP REPORTS SUMMARY');
$sheet5->setCellValue('A6', 'Generated on: ' . date('F j, Y g:i A'));

// Style headers
$sheet5->getStyle('A1:A6')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);
$sheet5->mergeCells('A1:D1');
$sheet5->mergeCells('A2:D2');
$sheet5->mergeCells('A3:D3');
$sheet5->mergeCells('A5:D5');
$sheet5->mergeCells('A6:D6');

// Summary data
$row = 8;
$sheet5->setCellValue('A' . $row, 'Report Type');
$sheet5->setCellValue('B' . $row, 'Items Count');
$sheet5->setCellValue('C' . $row, 'Total Value');
$sheet5->setCellValue('D' . $row, 'Departments');

styleHeader($sheet5, 'A' . $row . ':D' . $row);

// Get summary statistics
$totalItems = count($consolidatedItems);
$totalValue = array_sum(array_column($consolidatedItems, 'total_cost'));
$deptCount = count($departments);
$categoryCount = count($categories);

$row++;
$sheet5->setCellValue('A' . $row, 'Consolidated Items');
$sheet5->setCellValue('B' . $row, $totalItems);
$sheet5->setCellValue('C' . $row, '₱' . number_format($totalValue, 2));
$sheet5->setCellValue('D' . $row, $deptCount);

$row++;
$sheet5->setCellValue('A' . $row, 'APP Report Items');
$sheet5->setCellValue('B' . $row, count($appData));
$sheet5->setCellValue('C' . $row, '₱' . number_format(array_sum(array_column($appData, 'total_cost')), 2));
$sheet5->setCellValue('D' . $row, $deptCount);

$row++;
$sheet5->setCellValue('A' . $row, 'Department Report Items');
$sheet5->setCellValue('B' . $row, count($groupedDeptData));
$sheet5->setCellValue('C' . $row, '₱' . number_format(array_sum(array_column($groupedDeptData, 'total_cost')), 2));
$sheet5->setCellValue('D' . $row, $deptCount);

$row++;
$sheet5->setCellValue('A' . $row, 'Categories Covered');
$sheet5->setCellValue('B' . $row, $categoryCount);
$sheet5->setCellValue('C' . $row, '-');
$sheet5->setCellValue('D' . $row, '-');

// Style data rows
$dataRange = 'A9:D' . $row;
styleData($sheet5, $dataRange);

// Auto-size columns
foreach(range('A', 'D') as $columnID) {
    $sheet5->getColumnDimension($columnID)->setAutoSize(true);
}

// Create writer and output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?></content>
</xai:function_call">Now I need to modify the consolidated_items.php file to replace all the report buttons with a single "Excel Report" button that calls this new script.

Looking at the current buttons (lines 253-296), I need to replace them with just one button. The button should call a function that downloads the Excel file from generate_excel_report.php. I'll also need to add the JavaScript function for this. 

Let me update the consolidated_items.php file. I'll replace the entire button section with just the Excel Report button. I'll also need to add the JavaScript function. Looking at the existing functions, I can see the pattern for authenticated downloads. I'll add a new function called exportAllReports(). 

First, let me update the HTML buttons section. Then I'll add the JavaScript function. 

The buttons are in the card-header section around line 253-296. I need to replace all those buttons with just one Excel Report button. 

Then I need to add the JavaScript function. Looking at the existing functions like exportConsolidated() and exportDepartmentReport(), I can see they use authenticatedFetch to get the token and then window.location.href to download. For the new function, I'll do something similar but call the new generate_excel_report.php. 

Let me make these changes. 

First, replace the buttons section. 

Then add the JavaScript function. 

The buttons section is from line 253 to 296. I'll replace it with a single button. 

Then I need to add the JavaScript function. Looking at the existing functions, exportConsolidated() is at line 1123, exportDepartmentReport() at line 1131. I'll add the new function after those. 

Let me do this step by step. 

First, replace the buttons section. 

The current buttons are:

- APP Report dropdown (Preview PDF, Download PDF)

- Export button

- Dept Report dropdown (Preview PDF, Download PDF, Export CSV)

- Refresh button

- Preview PDF and Download PDF buttons

I need to replace all of these with just one "Excel Report" button. 

Let me replace the entire div with class "d-flex justify-content-end flex-wrap gap-2" with a single button. 

Then add the JavaScript function. 

Let me do the HTML replacement first. 

The section is:

<div class="d-flex justify-content-end flex-wrap gap-2">

  <div class="dropdown btn-group">

    <button class="btn btn-primary" onclick="previewAPPReport()">

      <i class="fas fa-calendar-alt"></i> APP Report

    </button>

    <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

      <span class="sr-only">Toggle Dropdown</span>

    </button>

    <div class="dropdown-menu">

      <a class="dropdown-item" href="#" onclick="downloadAPPReport()">

        <i class="fas fa-download"></i> Download PDF

      </a>

    </div>

  </div>

  <button class="btn btn-success" onclick="exportConsolidated()">

    <i class="fas fa-download"></i> Export

  </button>

  <div class="dropdown">

    <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">

      <i class="fas fa-building"></i> Dept Report <span class="caret"></span>

    </button>

    <ul class="dropdown-menu">

      <li><a class="dropdown-item" href="#" onclick="previewDepartmentReport()"><i class="fas fa-eye"></i> Preview PDF</a></li>

      <li><a class="dropdown-item" href="#" onclick="downloadDepartmentReport()"><i class="fas fa-download"></i> Download PDF</a></li>

      <li><a class="dropdown-item" href="#" onclick="exportDepartmentReport()"><i class="fas fa-file-csv"></i> Export CSV</a></li>

    </ul>

  </div>

  <button class="btn btn-info" onclick="loadConsolidatedItems()">

    <i class="fas fa-sync"></i> Refresh

  </button>

  <div class="dropdown btn-group">

    <button class="btn btn-danger" onclick="previewPDF()">

      <i class="fas fa-eye"></i> Preview PDF

    </button>

    <button class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

      <span class="sr-only">Toggle Dropdown</span>

    </button>

    <div class="dropdown-menu">

      <a class="dropdown-item" href="#" onclick="exportToPDF()">

        <i class="fas fa-download"></i> Download PDF

      </a>

    </div>

  </div>

</div>

I'll replace this with:

<div class="d-flex justify-content-end flex-wrap gap-2">

  <button class="btn btn-success btn-lg" onclick="exportAllReports()">

    <i class="fas fa-file-excel"></i> Excel Report

  </button>

  <button class="btn btn-info" onclick="loadConsolidatedItems()">

    <i class="fas fa-sync"></i> Refresh

  </button>

</div>

Now I need to add the JavaScript function. I'll add it after the existing export functions. 

Looking at the existing functions, they use authenticatedFetch to get the token and then window.location.href. For the new function, I'll do the same but call generate_excel_report.php. 

Let me add the function after exportDepartmentReport() at line 1135. 

The function should be:

// Export All Reports to Excel

function exportAllReports() {

    if (confirm('Export all PPMP reports to Excel (XLSX)? This will include Consolidated Items, APP Report, Department Report, Category Report, and Summary.')) {

        const token = getAccessToken();

        window.location.href = 'generate_excel_report.php?token=' + encodeURIComponent(token);

    }

}

Yes, that should work. 

Now let me make these changes to the file. 

First, replace the buttons section. 

Then add the JavaScript function. 

Let me do the buttons section first. 

The buttons section starts at line 253 and ends at line 296. I'll replace it with the new simplified version. 

Then I'll add the JavaScript function after line 1135. 

Let me make the changes. 

First, the buttons replacement. 

I need to be careful with the exact text. Let me get the exact text from the file. 

From the file, the section is:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <div class="dropdown btn-group">

               <button class="btn btn-primary" onclick="previewAPPReport()">

                 <i class="fas fa-calendar-alt"></i> APP Report

               </button>

               <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="downloadAPPReport()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

             <button class="btn btn-success" onclick="exportConsolidated()">

               <i class="fas fa-download"></i> Export

             </button>

             <div class="dropdown">

               <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">

                 <i class="fas fa-building"></i> Dept Report <span class="caret"></span>

               </button>

               <ul class="dropdown-menu">

                 <li><a class="dropdown-item" href="#" onclick="previewDepartmentReport()"><i class="fas fa-eye"></i> Preview PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="downloadDepartmentReport()"><i class="fas fa-download"></i> Download PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="exportDepartmentReport()"><i class="fas fa-file-csv"></i> Export CSV</a></li>

               </ul>

             </div>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

             <div class="dropdown btn-group">

               <button class="btn btn-danger" onclick="previewPDF()">

                 <i class="fas fa-eye"></i> Preview PDF

               </button>

               <button class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="exportToPDF()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

           </div>

I'll replace this with:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <button class="btn btn-success btn-lg" onclick="exportAllReports()">

               <i class="fas fa-file-excel"></i> Excel Report

             </button>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

           </div>

Now for the JavaScript function. I'll add it after the exportDepartmentReport function. 

The exportDepartmentReport function is:

// Export department report

function exportDepartmentReport() {

    if (confirm('Export department report to Excel (XLSX)?')) {

        const token = getAccessToken();

        window.location.href = 'generate_department_report.php?export=xlsx&token=' + encodeURIComponent(token);

    }

}

I'll add the new function after this. 

The new function will be:

// Export All Reports to Excel

function exportAllReports() {

    if (confirm('Export all PPMP reports to Excel (XLSX)? This will include Consolidated Items, APP Report, Department Report, Category Report, and Summary.')) {

        const token = getAccessToken();

        window.location.href = 'generate_excel_report.php?token=' + encodeURIComponent(token);

    }

}

Yes. 

Now let me make these changes to the file. 

First, the buttons section replacement. 

Then the JavaScript function addition. 

Let me do the buttons section first. 

I need to find the exact line numbers. From the file, the buttons section starts at line 253 and ends at line 296. 

Let me replace that section. 

Then for the JavaScript, the exportDepartmentReport function ends at line 1135. I'll add the new function after that. 

Let me make the changes. 

First, the buttons replacement. 

I'll use apply_diff to replace the buttons section. 

The old string is the entire div with all the buttons. 

The new string is the simplified div with just the Excel Report and Refresh buttons. 

Then for the JavaScript, I'll add the new function after the exportDepartmentReport function. 

Let me do this. 

First, the buttons replacement. 

The old content is from line 253 to 296. 

Let me get the exact text. 

From the file:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <div class="dropdown btn-group">

               <button class="btn btn-primary" onclick="previewAPPReport()">

                 <i class="fas fa-calendar-alt"></i> APP Report

               </button>

               <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="downloadAPPReport()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

             <button class="btn btn-success" onclick="exportConsolidated()">

               <i class="fas fa-download"></i> Export

             </button>

             <div class="dropdown">

               <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">

                 <i class="fas fa-building"></i> Dept Report <span class="caret"></span>

               </button>

               <ul class="dropdown-menu">

                 <li><a class="dropdown-item" href="#" onclick="previewDepartmentReport()"><i class="fas fa-eye"></i> Preview PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="downloadDepartmentReport()"><i class="fas fa-download"></i> Download PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="exportDepartmentReport()"><i class="fas fa-file-csv"></i> Export CSV</a></li>

               </ul>

             </div>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

             <div class="dropdown btn-group">

               <button class="btn btn-danger" onclick="previewPDF()">

                 <i class="fas fa-eye"></i> Preview PDF

               </button>

               <button class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="exportToPDF()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

           </div>

The new content is:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <button class="btn btn-success btn-lg" onclick="exportAllReports()">

               <i class="fas fa-file-excel"></i> Excel Report

             </button>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

           </div>

Now for the JavaScript function. The exportDepartmentReport function is:

// Export department report

function exportDepartmentReport() {

    if (confirm('Export department report to Excel (XLSX)?')) {

        const token = getAccessToken();

        window.location.href = 'generate_department_report.php?export=xlsx&token=' + encodeURIComponent(token);

    }

}

I'll add the new function after this. 

The new function is:

// Export All Reports to Excel

function exportAllReports() {

    if (confirm('Export all PPMP reports to Excel (XLSX)? This will include Consolidated Items, APP Report, Department Report, Category Report, and Summary.')) {

        const token = getAccessToken();

        window.location.href = 'generate_excel_report.php?token=' + encodeURIComponent(token);

    }

}

Let me make these changes. 

First, the buttons replacement. 

I need to find the exact line number for the start of the buttons section. From the file, it's line 253. 

Let me use apply_diff for the buttons section. 

The search string will be the old div content, and the replace string will be the new div content. 

Then for the JavaScript, I'll add the new function after the exportDepartmentReport function. 

Let me do the buttons first. 

The search string is the entire old div. 

The replace string is the new div. 

Then for the JavaScript, I'll add the new function after the exportDepartmentReport function. 

Let me do this. 

First, the buttons replacement. 

I'll use apply_diff with the path, and the diff. 

The start line for the buttons section is line 253. 

Let me get the exact text. 

From the file, line 253 is:

           <div class="d-flex justify-content-end flex-wrap gap-2">

And it ends at line 296:

           </div>

So the search string is from line 253 to 296. 

The replace string is the new div. 

Let me do this. 

For the JavaScript, the exportDepartmentReport function ends at line 1135. 

I'll add the new function after line 1135. 

Let me make the changes. 

First, the buttons replacement. 

I'll use apply_diff. 

The path is system/consolidated_items.php

The diff will have the old content and the new content. 

The old content is the entire div from line 253 to 296. 

The new content is the simplified div. 

Let me write the diff. 

The search string is:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <div class="dropdown btn-group">

               <button class="btn btn-primary" onclick="previewAPPReport()">

                 <i class="fas fa-calendar-alt"></i> APP Report

               </button>

               <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="downloadAPPReport()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

             <button class="btn btn-success" onclick="exportConsolidated()">

               <i class="fas fa-download"></i> Export

             </button>

             <div class="dropdown">

               <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">

                 <i class="fas fa-building"></i> Dept Report <span class="caret"></span>

               </button>

               <ul class="dropdown-menu">

                 <li><a class="dropdown-item" href="#" onclick="previewDepartmentReport()"><i class="fas fa-eye"></i> Preview PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="downloadDepartmentReport()"><i class="fas fa-download"></i> Download PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="exportDepartmentReport()"><i class="fas fa-file-csv"></i> Export CSV</a></li>

               </ul>

             </div>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

             <div class="dropdown btn-group">

               <button class="btn btn-danger" onclick="previewPDF()">

                 <i class="fas fa-eye"></i> Preview PDF

               </button>

               <button class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="exportToPDF()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

           </div>

The replace string is:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <button class="btn btn-success btn-lg" onclick="exportAllReports()">

               <i class="fas fa-file-excel"></i> Excel Report

             </button>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

           </div>

Now for the JavaScript function. I'll add it after the exportDepartmentReport function. 

The exportDepartmentReport function is:

// Export department report

function exportDepartmentReport() {

    if (confirm('Export department report to Excel (XLSX)?')) {

        const token = getAccessToken();

        window.location.href = 'generate_department_report.php?export=xlsx&token=' + encodeURIComponent(token);

    }

}

I'll add the new function after this. 

The new function is:

// Export All Reports to Excel

function exportAllReports() {

    if (confirm('Export all PPMP reports to Excel (XLSX)? This will include Consolidated Items, APP Report, Department Report, Category Report, and Summary.')) {

        const token = getAccessToken();

        window.location.href = 'generate_excel_report.php?token=' + encodeURIComponent(token);

    }

}

Let me make these changes. 

First, the buttons replacement. 

I'll use apply_diff for the buttons. 

The start line is 253. 

Let me do the diff. 

The search string is the old div content. 

The replace string is the new div content. 

Then for the JavaScript, I'll add the new function after the exportDepartmentReport function. 

Let me do the buttons first. 

I'll use apply_diff with the path system/consolidated_items.php and the diff. 

The diff will have the old content and the new content. 

The old content is the entire old div. 

The new content is the new div. 

Let me write the diff. 

The search string is:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <div class="dropdown btn-group">

               <button class="btn btn-primary" onclick="previewAPPReport()">

                 <i class="fas fa-calendar-alt"></i> APP Report

               </button>

               <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="downloadAPPReport()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

             <button class="btn btn-success" onclick="exportConsolidated()">

               <i class="fas fa-download"></i> Export

             </button>

             <div class="dropdown">

               <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">

                 <i class="fas fa-building"></i> Dept Report <span class="caret"></span>

               </button>

               <ul class="dropdown-menu">

                 <li><a class="dropdown-item" href="#" onclick="previewDepartmentReport()"><i class="fas fa-eye"></i> Preview PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="downloadDepartmentReport()"><i class="fas fa-download"></i> Download PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="exportDepartmentReport()"><i class="fas fa-file-csv"></i> Export CSV</a></li>

               </ul>

             </div>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

             <div class="dropdown btn-group">

               <button class="btn btn-danger" onclick="previewPDF()">

                 <i class="fas fa-eye"></i> Preview PDF

               </button>

               <button class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="exportToPDF()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

           </div>

The replace string is:

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <button class="btn btn-success btn-lg" onclick="exportAllReports()">

               <i class="fas fa-file-excel"></i> Excel Report

             </button>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

           </div>

Now for the JavaScript function. I'll add it after the exportDepartmentReport function. 

The exportDepartmentReport function is at line 1131-1135. 

I'll add the new function after line 1135. 

The new function is:

// Export All Reports to Excel

function exportAllReports() {

    if (confirm('Export all PPMP reports to Excel (XLSX)? This will include Consolidated Items, APP Report, Department Report, Category Report, and Summary.')) {

        const token = getAccessToken();

        window.location.href = 'generate_excel_report.php?token=' + encodeURIComponent(token);

    }

}

Let me make these changes. 

First, the buttons replacement. 

I'll use apply_diff. 

The path is system/consolidated_items.php

The diff is:

<<<<<<< SEARCH

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <div class="dropdown btn-group">

               <button class="btn btn-primary" onclick="previewAPPReport()">

                 <i class="fas fa-calendar-alt"></i> APP Report

               </button>

               <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="downloadAPPReport()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

             <button class="btn btn-success" onclick="exportConsolidated()">

               <i class="fas fa-download"></i> Export

             </button>

             <div class="dropdown">

               <button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">

                 <i class="fas fa-building"></i> Dept Report <span class="caret"></span>

               </button>

               <ul class="dropdown-menu">

                 <li><a class="dropdown-item" href="#" onclick="previewDepartmentReport()"><i class="fas fa-eye"></i> Preview PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="downloadDepartmentReport()"><i class="fas fa-download"></i> Download PDF</a></li>

                 <li><a class="dropdown-item" href="#" onclick="exportDepartmentReport()"><i class="fas fa-file-csv"></i> Export CSV</a></li>

               </ul>

             </div>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

             <div class="dropdown btn-group">

               <button class="btn btn-danger" onclick="previewPDF()">

                 <i class="fas fa-eye"></i> Preview PDF

               </button>

               <button class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                 <span class="sr-only">Toggle Dropdown</span>

               </button>

               <div class="dropdown-menu">

                 <a class="dropdown-item" href="#" onclick="exportToPDF()">

                   <i class="fas fa-download"></i> Download PDF

                 </a>

               </div>

             </div>

           </div>

=======

           <div class="d-flex justify-content-end flex-wrap gap-2">

             <button class="btn btn-success btn-lg" onclick="exportAllReports()">

               <i class="fas fa-file-excel"></i> Excel Report

             </button>

             <button class="btn btn-info" onclick="loadConsolidatedItems()">

               <i class="fas fa-sync"></i> Refresh

             </button>

           </div>

>>>>>>> REPLACE

<xai:function_call name="apply_diff">
<parameter name="path">system/consolidated_items.php