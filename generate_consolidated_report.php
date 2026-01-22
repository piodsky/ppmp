<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload dependencies
use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../apiPPMP');
$dotenv->load();

require_once '../apiPPMP/config.php';

// Check for token in cookie or Authorization header
$token = null;

// First check for token in cookie (secure method)
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
}
// Fallback to Authorization header
elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
        $token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
    }
}

if (!$token) {
    header("Location: login.php");
    exit();
}

// Validate token via API call
$apiUrl = $_ENV['API_BASE_URL'] . '/api_verify_token.php';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ],
        'timeout' => 10
    ]
]);

$response = file_get_contents($apiUrl, false, $context);
if ($response === false) {
    header("Location: login.php");
    exit();
}

$data = json_decode($response, true);
if (!$data || $data['status'] !== 'success') {
    header("Location: login.php");
    exit();
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Check export type
$exportType = isset($_GET['export']) ? $_GET['export'] : 'pdf';

// Check if user data is valid
$user = $data['user'] ?? null;
if (!$user) {
    if ($exportType === 'csv') {
        header('Content-Type: text/plain');
        echo 'Access Denied: Please log in first';
        exit();
    } elseif ($exportType === 'xlsx') {
        // For XLSX, create error spreadsheet
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Access_Denied_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Access Denied: Please log in first');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    } else {
        // Default to PDF
        header('Content-Type: application/pdf');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        $pdf = new ConsolidatedPDF('L', 'mm', 'Legal');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(0, 20, 'Access Denied: Please log in first', 0, 1, 'C');
        $pdf->Output('Access_Denied.pdf', 'I');
        exit();
    }
}

// Set headers based on export type
if ($exportType === 'xlsx') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: inline;filename="Consolidated_PPMP_Report_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Cache-Control: max-age=0');
} elseif ($exportType === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="Consolidated_PPMP_Report_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Cache-Control: max-age=0');
} else {
    // Default to PDF
    header('Content-Type: application/pdf');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
}

// Prevent any HTML output
ob_start();

// Check if FPDF library exists
$fpdf_path = __DIR__ . "/fpdf186/fpdf.php";
if (!file_exists($fpdf_path)) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Create error PDF without FPDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="error.pdf"');

    // Simple PDF creation without FPDF
    echo "%PDF-1.4\n";
    echo "1 0 obj\n";
    echo "<<\n";
    echo "/Type /Catalog\n";
    echo "/Pages 2 0 R\n";
    echo ">>\n";
    echo "endobj\n";
    echo "2 0 obj\n";
    echo "<<\n";
    echo "/Type /Pages\n";
    echo "/Kids [3 0 R]\n";
    echo "/Count 1\n";
    echo ">>\n";
    echo "endobj\n";
    echo "3 0 obj\n";
    echo "<<\n";
    echo "/Type /Page\n";
    echo "/Parent 2 0 R\n";
    echo "/MediaBox [0 0 612 792]\n";
    echo "/Contents 4 0 R\n";
    echo "/Resources <<\n";
    echo "/Font <<\n";
    echo "/F1 5 0 R\n";
    echo ">>\n";
    echo ">>\n";
    echo ">>\n";
    echo "endobj\n";
    echo "4 0 obj\n";
    echo "<<\n";
    echo "/Length 44\n";
    echo ">>\n";
    echo "stream\n";
    echo "BT\n";
    echo "/F1 12 Tf\n";
    echo "100 700 Td\n";
    echo "(FPDF Library Not Found) Tj\n";
    echo "ET\n";
    echo "endstream\n";
    echo "endobj\n";
    echo "5 0 obj\n";
    echo "<<\n";
    echo "/Type /Font\n";
    echo "/Subtype /Type1\n";
    echo "/BaseFont /Helvetica\n";
    echo ">>\n";
    echo "endobj\n";
    echo "xref\n";
    echo "0 6\n";
    echo "0000000000 65535 f \n";
    echo "0000000009 00000 n \n";
    echo "0000000058 00000 n \n";
    echo "0000000115 00000 n \n";
    echo "0000000274 00000 n \n";
    echo "0000000368 00000 n \n";
    echo "trailer\n";
    echo "<<\n";
    echo "/Size 6\n";
    echo "/Root 1 0 R\n";
    echo ">>\n";
    echo "startxref\n";
    echo "462\n";
    echo "%%EOF\n";
    exit();
}

require_once "fpdf186/fpdf.php";

// Extend FPDF class for Consolidated Report
class ConsolidatedPDF extends FPDF {
    public $currentYear;

    function __construct($orientation = 'L', $unit = 'mm', $size = 'Legal', $currentYear = null) {
        $this->currentYear = $currentYear ?: date('Y');
        parent::__construct($orientation, $unit, $size);
    }

    function Header() {
        if ($this->PageNo() == 1) {
            // Logo on the left
            $logoPath = __DIR__ . '/image/citylogo.png';
            if (file_exists($logoPath)) {
                $this->Image('image/citylogo.png', 10, 8, 15);
            }

            // Government header
            $this->SetFont('Arial', 'B', 12);
            $this->SetTextColor(0, 51, 102);
            $this->Cell(0, 6, 'Republic of the Philippines', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(0, 5, 'Province of Bukidnon', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(0, 4, 'City of Malaybalay', 0, 1, 'C');

            // Logo on the right
            if (file_exists($logoPath)) {
                $this->Image('image/citylogo.png', 320, 8, 15);
            }

            // Main title
            $this->Ln(2);
            $this->SetFont('Arial', 'B', 10);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 6, 'CONSOLIDATED PPMP ITEMS BY DEPARTMENT', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(0, 5, $this->currentYear . ' REPORT', 0, 1, 'C');

            $this->Ln(3);
        }
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 5, 'Generated by PPMP Management System - Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += isset($cw[$c]) ? $cw[$c] : 0;
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                } else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    // Table header for consolidated report
    function TableHeader($departments) {
        $this->SetFont('Arial', 'B', 6);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(0, 0, 0);

        // Fixed columns
        $this->Cell(16, 10, 'Item Code', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Item & Specifications', 1, 0, 'C', true);
        $this->Cell(12, 10, 'Unit', 1, 0, 'C', true);

        // Department columns
        $deptWidth = 25;
        foreach($departments as $dept) {
            $this->Cell($deptWidth, 10, substr($dept, 0, 10), 1, 0, 'C', true);
        }
        $this->Cell(20, 10, 'Total Qty', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Unit Cost', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Total Cost', 1, 1, 'C', true);
    }

    // Table row
    function TableRow($data, $departments) {
        $this->SetFont('Arial', '', 5);
        $this->SetTextColor(0, 0, 0);

        $widths = array_merge([16, 50, 12], array_fill(0, count($departments), 25), [20, 25, 25]);

        // Calculate max lines for the row
        $max_lines = 1;
        for($i = 0; $i < count($data); $i++) {
            $lines = $this->NbLines($widths[$i], $data[$i]);
            $max_lines = max($max_lines, $lines);
        }
        $height = $max_lines * 4;

        $startY = $this->GetY();
        for($i = 0; $i < count($data); $i++) {
            $x = $this->GetX();
            $align = ($i >= 3 && $i < 3 + count($departments)) ? 'C' : 'L';
            $this->MultiCell($widths[$i], 4, $data[$i], 1, $align);
            $this->SetXY($x + $widths[$i], $startY);
        }
        $this->Ln($height);
    }
}


// Check if preview mode is requested
$preview = isset($_GET['preview']) && $_GET['preview'] == '1';

// Get the year from PPMP documents first
$yearStmt = $conn->prepare("SELECT DISTINCT Plan_Year FROM tbl_ppmp_documents WHERE Status = 'approved' ORDER BY Plan_Year DESC LIMIT 1");
$yearStmt->execute();
$yearResult = $yearStmt->fetch(PDO::FETCH_ASSOC);
$currentYear = $yearResult ? $yearResult['Plan_Year'] : date('Y');

// Check if there are any approved PPMP documents
$checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_ppmp_documents WHERE Status = 'approved'");
$checkStmt->execute();
$checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
$approvedCount = $checkResult['count'];

// Get all departments with approved PPMPs
$deptStmt = $conn->prepare("
    SELECT DISTINCT Department
    FROM tbl_ppmp_documents
    WHERE Status = 'approved' AND Plan_Year = ?
    ORDER BY Department
");
$deptStmt->execute([$currentYear]);
$departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

// Get consolidated items data (aggregated) for CSV/XLSX export
$exportStmt = $conn->prepare("
    SELECT
        pi.Item_Code,
        pi.Item_Name,
        pi.Item_Description,
        pi.Unit,
        pi.Unit_Cost,
        SUM(pi.Jan_Qty + pi.Feb_Qty + pi.Mar_Qty + pi.Apr_Qty + pi.May_Qty + pi.Jun_Qty + pi.Jul_Qty + pi.Aug_Qty + pi.Sep_Qty + pi.Oct_Qty + pi.Nov_Qty + pi.Dec_Qty) as total_quantity,
        SUM(pi.Total_Cost) as total_cost,
        GROUP_CONCAT(DISTINCT p.PPMP_Number ORDER BY p.PPMP_Number) as ppmp_numbers,
        i.Category
    FROM tbl_ppmp_entries pi
    JOIN tbl_ppmp_documents p ON pi.PPMP_ID = p.ID
    JOIN tbl_ppmp_bac_items i ON pi.Item_ID = i.ID
    WHERE p.Status = 'approved'
    GROUP BY pi.Item_Code, pi.Item_Name, pi.Item_Description, pi.Unit, pi.Unit_Cost, i.Category
    ORDER BY i.Category, pi.Item_Name
");
$exportStmt->execute();
$groupedItems = $exportStmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new ConsolidatedPDF('L', 'mm', 'Legal', $currentYear);
$pdf->AliasNbPages();
$pdf->AddPage();

// Check if there are approved PPMP documents
if ($approvedCount == 0) {
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 20, 'No Approved PPMP Documents Found', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 10, 'The consolidated report requires approved PPMP documents to generate procurement data. Please ensure you have PPMP documents with "approved" status before generating the report.', 0, 'L');
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Contact your system administrator if you need assistance.', 0, 1, 'L');

    $filename = 'Consolidated_Report_Error_' . date('Y-m-d_H-i-s') . '.pdf';
    if ($preview) {
        $pdf->Output($filename, 'I');
    } else {
        $pdf->Output($filename, 'D');
    }
    exit();
}

if (empty($departments)) {
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 20, 'No Departments Found', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 10, 'No departments with approved PPMP documents found for the selected year.', 0, 'L');

    $filename = 'Consolidated_Report_No_Depts_' . date('Y-m-d_H-i-s') . '.pdf';
    if ($preview) {
        $pdf->Output($filename, 'I');
    } else {
        $pdf->Output($filename, 'D');
    }
    exit();
}

if (empty($groupedItems)) {
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 20, 'No Procurement Items Found', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 10, 'The approved PPMP documents do not contain any procurement items. Please ensure your PPMP documents have been properly created with items before generating the consolidated report.', 0, 'L');
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Approved PPMP documents found: ' . $approvedCount, 0, 1, 'L');
    $pdf->Cell(0, 10, 'Year: ' . $currentYear, 0, 1, 'L');

    $filename = 'Consolidated_Report_No_Items_' . date('Y-m-d_H-i-s') . '.pdf';
    if ($preview) {
        $pdf->Output($filename, 'I');
    } else {
        $pdf->Output($filename, 'D');
    }
    exit();
}

try {
    // Get raw items data for PDF (with department breakdown)
    $pdfStmt = $conn->prepare("
        SELECT
            pi.Item_Code,
            pi.Item_Name,
            pi.Item_Description,
            pi.Unit,
            pi.Unit_Cost,
            p.Department,
            SUM(pi.Jan_Qty + pi.Feb_Qty + pi.Mar_Qty + pi.Apr_Qty + pi.May_Qty + pi.Jun_Qty + pi.Jul_Qty + pi.Aug_Qty + pi.Sep_Qty + pi.Oct_Qty + pi.Nov_Qty + pi.Dec_Qty) as total_quantity,
            SUM(pi.Total_Cost) as total_cost
        FROM tbl_ppmp_entries pi
        JOIN tbl_ppmp_documents p ON pi.PPMP_ID = p.ID
        WHERE p.Status = 'approved'
        GROUP BY pi.Item_Code, pi.Item_Name, pi.Item_Description, pi.Unit, pi.Unit_Cost, p.Department
        ORDER BY pi.Item_Code, pi.Item_Name, p.Department
    ");
    $pdfStmt->execute();
    $rawItems = $pdfStmt->fetchAll(PDO::FETCH_ASSOC);

    // Group items by Item_Code and Item_Name for PDF
    $pdfGroupedItems = [];
    foreach($rawItems as $item) {
        $key = $item['Item_Code'] . '|' . $item['Item_Name'] . '|' . $item['Item_Description'] . '|' . $item['Unit'] . '|' . $item['Unit_Cost'];
        if (!isset($pdfGroupedItems[$key])) {
            $pdfGroupedItems[$key] = [
                'Item_Code' => $item['Item_Code'],
                'Item_Name' => $item['Item_Name'],
                'Item_Description' => $item['Item_Description'],
                'Unit' => $item['Unit'],
                'Unit_Cost' => $item['Unit_Cost'],
                'departments' => [],
                'total_quantity' => 0,
                'total_cost' => 0
            ];
        }
        $pdfGroupedItems[$key]['departments'][$item['Department']] = $item['total_quantity'];
        $pdfGroupedItems[$key]['total_quantity'] += $item['total_quantity'];
        $pdfGroupedItems[$key]['total_cost'] += $item['total_cost'];
    }

    // Table header
    $pdf->TableHeader($departments);

    // Table data
    foreach($pdfGroupedItems as $item) {
        $data = [
            $item['Item_Code'] ?: '',
            $item['Item_Name'] . ' - ' . $item['Item_Description'],
            $item['Unit'] ?: ''
        ];

        // Add department quantities
        foreach($departments as $dept) {
            $qty = isset($item['departments'][$dept]) ? $item['departments'][$dept] : '';
            $data[] = $qty === '' ? '' : number_format($qty);
        }

        // Add totals
        $data[] = number_format($item['total_quantity']);
        $data[] = 'P' . number_format($item['Unit_Cost'], 2);
        $data[] = 'P' . number_format($item['total_cost'], 2);

        $pdf->TableRow($data, $departments);

        // Check if we need a new page
        if($pdf->GetY() > 170) {
            $pdf->AddPage();
            $pdf->TableHeader($departments);
        }
    }

    // Summary section
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(0, 6, 'SUMMARY', 0, 1, 'L');

    $pdf->SetFont('Arial', '', 7);
    $totalItems = count($groupedItems);
    $grandTotalCost = array_sum(array_column($groupedItems, 'total_cost'));

    $pdf->Cell(70, 5, 'Total Unique Items:', 1, 0, 'L');
    $pdf->Cell(25, 5, number_format($totalItems), 1, 1, 'R');

    $pdf->Cell(70, 5, 'Grand Total Cost:', 1, 0, 'L');
    $pdf->Cell(25, 5, 'P' . number_format($grandTotalCost, 2), 1, 1, 'R');

    $pdf->Cell(70, 5, 'Approved PPMP Documents:', 1, 0, 'L');
    $pdf->Cell(25, 5, number_format($approvedCount), 1, 1, 'R');

} catch (Exception $e) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Create error PDF
    $pdf = new ConsolidatedPDF('L', 'mm', 'Legal');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 20, 'Error Generating Consolidated Report', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 10, 'An error occurred while generating the PDF report: ' . $e->getMessage(), 0, 'L');
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Please contact your system administrator.', 0, 1, 'L');
}

// Output based on export type
if ($exportType === 'xlsx') {
    // Clear any output buffers before Excel generation
    while (ob_get_level()) {
        ob_end_clean();
    }
    try {
        // Generate XLSX file
        generateXLSX($groupedItems, $departments, $currentYear, $approvedCount);
    } catch (Exception $e) {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Create error Excel file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Error_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Error Generating Excel Report');
        $sheet->setCellValue('A2', 'Error: ' . $e->getMessage());

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
} elseif ($exportType === 'csv') {
    // Generate CSV file
    generateCSV($groupedItems, $departments, $currentYear, $approvedCount);
} else {
    // Default PDF output
    $filename = 'Consolidated_PPMP_Report_' . date('Y-m-d_H-i-s') . '.pdf';

    if ($preview) {
        // Preview mode - display in browser
        $pdf->Output($filename, 'I');
    } else {
        // Download mode - force download
        $pdf->Output($filename, 'D');
    }
}

// Function to generate XLSX export
function generateXLSX($groupedItems, $departments, $currentYear, $approvedCount) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Consolidated Items');

    // Headers
    $sheet->setCellValue('A1', 'Item Code');
    $sheet->setCellValue('B1', 'Item Name');
    $sheet->setCellValue('C1', 'Description');
    $sheet->setCellValue('D1', 'Unit');
    $sheet->setCellValue('E1', 'Unit Cost');
    $sheet->setCellValue('F1', 'Total Quantity');
    $sheet->setCellValue('G1', 'Total Cost');
    $sheet->setCellValue('H1', 'PPMP Numbers');

    // Style header
    $sheet->getStyle('A1:H1')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCCCCC']],
    ]);

    $row = 2;
    foreach ($groupedItems as $item) {
        $sheet->setCellValue('A' . $row, $item['Item_Code']);
        $sheet->setCellValue('B' . $row, $item['Item_Name']);
        $sheet->setCellValue('C' . $row, $item['Item_Description']);
        $sheet->setCellValue('D' . $row, $item['Unit']);
        $sheet->setCellValue('E' . $row, $item['Unit_Cost']);
        $sheet->setCellValue('F' . $row, $item['total_quantity']);
        $sheet->setCellValue('G' . $row, $item['total_cost']);
        $sheet->setCellValue('H' . $row, $item['ppmp_numbers']);
        $row++;
    }

    // Auto-size columns
    foreach(range('A', 'H') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

// Function to generate CSV export
function generateCSV($groupedItems, $departments, $currentYear, $approvedCount) {
    // Output UTF-8 BOM for proper character encoding in Excel
    echo "\xEF\xBB\xBF";

    // Output CSV headers
    echo "Item Code,Item Name,Description,Unit,Unit Cost,Total Quantity,Total Cost,PPMP Numbers\n";
    foreach ($groupedItems as $item) {
        echo '"' . str_replace('"', '""', $item['Item_Code']) . '",';
        echo '"' . str_replace('"', '""', $item['Item_Name']) . '",';
        echo '"' . str_replace('"', '""', $item['Item_Description']) . '",';
        echo '"' . str_replace('"', '""', $item['Unit']) . '",';
        echo $item['Unit_Cost'] . ',';
        echo $item['total_quantity'] . ',';
        echo $item['total_cost'] . ',';
        echo '"' . str_replace('"', '""', $item['ppmp_numbers']) . '"' . "\n";
    }
}

// Helper function to convert column number to Excel column name (A, B, C, ..., AA, AB, etc.)
function getExcelColumnName($columnNumber) {
    $columnName = '';
    while ($columnNumber >= 0) {
        $columnName = chr(65 + ($columnNumber % 26)) . $columnName;
        $columnNumber = floor($columnNumber / 26) - 1;
    }
    return $columnName;
}
?>