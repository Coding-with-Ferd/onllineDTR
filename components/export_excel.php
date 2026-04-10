<?php
require '../vendor/autoload.php';
include '../backend/profile.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$attendanceRows = [];
if ($attendance_history && $attendance_history->num_rows > 0) {
    mysqli_data_seek($attendance_history, 0);
    while ($row = $attendance_history->fetch_assoc()) {
        $attendanceRows[] = $row;
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('DTR');

// Column widths
$sheet->getColumnDimension('A')->setWidth(18);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(14);
$sheet->getColumnDimension('H')->setWidth(51);

// Row heights
$sheet->getRowDimension(1)->setRowHeight(100);
$sheet->getRowDimension(2)->setRowHeight(10);


// Merge title/header areas
$sheet->mergeCells('A1:H1');
$sheet->mergeCells('A2:H2');

// Clinic title
$logoPath = '../assets/images/excel.png';

if (file_exists($logoPath)) {
    $drawing = new Drawing();
    $drawing->setPath($logoPath);

    // Adjust size
    $drawing->setHeight(120);

    // Position
    $drawing->setCoordinates('A1');

    // Move slightly right/down if needed
    $drawing->setOffsetX(3);
    $drawing->setOffsetY(5);

    $drawing->setWorksheet($sheet);
}

$branchName = 'MAIN';

if (!empty($emp['branch'])) {
    $branchName = $emp['branch'];
} elseif (!empty($emp['branch_id'])) {
    $branchStmt = $conn->prepare("SELECT branch_name FROM branches WHERE id = ?");
    $branchStmt->bind_param("i", $emp['branch_id']);
    $branchStmt->execute();
    $branchResult = $branchStmt->get_result()->fetch_assoc();

    if (!empty($branchResult['branch_name'])) {
        $branchName = $branchResult['branch_name'];
    }
}


// Meta section
$sheet->setCellValue('A3', 'NAME:');
$sheet->setCellValue('B3', strtoupper($emp['last_name'] . ', ' . $emp['first_name']));
$sheet->mergeCells('B3:H3');

$sheet->setCellValue('A4', 'DESIGNATION:');
$sheet->setCellValue('B4', strtoupper($emp['position']));
$sheet->mergeCells('B4:H4');

$sheet->setCellValue('A5', 'BRANCH:');
$sheet->setCellValue('B5', strtoupper($branchName));
$sheet->mergeCells('B5:H5');

$sheet->setCellValue('A6', 'CUT-OFF PERIOD:');
$cutoff = (!empty($_GET['start_date']) && !empty($_GET['end_date']))
    ? strtoupper(date('F d, Y', strtotime($_GET['start_date'])) . ' - ' . date('F d, Y', strtotime($_GET['end_date'])))
    : 'ALL RECORDS';
$sheet->setCellValue('B6', $cutoff);
$sheet->mergeCells('B6:H6');

// Meta borders
$sheet->getStyle('A3:H6')->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ],
    'font' => ['bold' => false]
]);

$sheet->getStyle('B3:H6')->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

$sheet->getStyle('A3:A6')->getFont()->setBold(true);

// Table header
$sheet->mergeCells('E8:F8');

$sheet->setCellValue('A8', 'DAY');
$sheet->setCellValue('B8', 'DATE');
$sheet->setCellValue('C8', 'TIME IN');
$sheet->setCellValue('D8', 'TIME OUT');
$sheet->setCellValue('E8', 'OVERTIME');
$sheet->setCellValue('G8', 'OT HOURS');
$sheet->setCellValue('H8', 'REMARKS');

$sheet->setCellValue('E9', 'FROM');
$sheet->setCellValue('F9', 'TO');

$sheet->mergeCells('A8:A9');
$sheet->mergeCells('B8:B9');
$sheet->mergeCells('C8:C9');
$sheet->mergeCells('D8:D9');
$sheet->mergeCells('G8:G9');
$sheet->mergeCells('H8:H9');

$sheet->getStyle('A8:H9')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['rgb' => 'EAD58D']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

// Attendance rows
$rowNum = 11;
$dayNo = 1;
$daysRendered = 0;
$totalOtHours = 0;

foreach ($attendanceRows as $row) {
    $sheet->setCellValue("A{$rowNum}", $dayNo++);
    $sheet->setCellValue("B{$rowNum}", date('n/j/Y', strtotime($row['attendance_date'])));
    $sheet->setCellValue("C{$rowNum}", !empty($row['time_in']) ? date('G:i', strtotime($row['time_in'])) : '');
    $sheet->setCellValue("D{$rowNum}", !empty($row['time_out']) ? date('G:i', strtotime($row['time_out'])) : '');

    $otFrom = '';
    $otTo = '';
    $otHours = '';

    if (!empty($row['time_in']) || !empty($row['time_out'])) {
        $daysRendered += 1;
    }

    $sheet->setCellValue("E{$rowNum}", $otFrom);
    $sheet->setCellValue("F{$rowNum}", $otTo);
    $sheet->setCellValue("G{$rowNum}", $otHours);
    $sheet->setCellValue("H{$rowNum}", $row['remarks'] ?? '');

    $rowNum++;
}

// Fill up to 16 rows
while ($dayNo <= 16) {
    $sheet->setCellValue("A{$rowNum}", $dayNo++);
    $rowNum++;
}

// Borders for body
$sheet->getStyle("A11:H" . ($rowNum - 1))->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

$sheet->getStyle("H11:H" . ($rowNum - 1))
      ->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Totals row
$sheet->mergeCells("A{$rowNum}:B{$rowNum}");
$sheet->mergeCells("C{$rowNum}:D{$rowNum}");
$sheet->mergeCells("E{$rowNum}:G{$rowNum}");

$sheet->setCellValue("A{$rowNum}", 'TOTAL NO. OF DAYS RENDERED');
$sheet->setCellValue("C{$rowNum}", $daysRendered);
$sheet->setCellValue("E{$rowNum}", 'TOTAL OT HOURS:');
$sheet->setCellValue("H{$rowNum}", $totalOtHours);

$sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['rgb' => 'EAD58D']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

$rowNum += 2;

// Certification
$sheet->mergeCells("A{$rowNum}:H{$rowNum}");
$sheet->setCellValue("A{$rowNum}", 'THIS IS TO CERTIFY THAT THE ABOVE INFORMATION IS CORRECT.');
$sheet->getStyle("A{$rowNum}")->applyFromArray([
    'font' => ['bold' => true, 'italic' => true, 'size' => 14],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

$rowNum += 3;

// Row 1: names with top border
$sheet->mergeCells("B{$rowNum}:D{$rowNum}");
$sheet->mergeCells("H{$rowNum}:H{$rowNum}");

$middleInitial = !empty($emp['middle_name']) 
    ? strtoupper(substr($emp['middle_name'], 0, 1)) . '.' 
    : '';

$sheet->setCellValue(
    "B{$rowNum}",
    strtoupper($emp['last_name'] . ', ' . $emp['first_name'] . ' ' . $middleInitial)
);
$sheet->setCellValue("H{$rowNum}", 'MALLORCA - BUALAT, JOHANNA MERYL H.'); 

$sheet->getStyle("B{$rowNum}:D{$rowNum}")->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

$sheet->getStyle("H{$rowNum}:H{$rowNum}")->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);


$rowNum += 0;

$sheet->mergeCells("A{$rowNum}:A" . ($rowNum + 1));
$sheet->mergeCells("F{$rowNum}:G" . ($rowNum + 1)); 

$sheet->setCellValue("A{$rowNum}", 'PREPARED BY:');
$sheet->setCellValue("F{$rowNum}", 'APPROVED BY:');

$sheet->getStyle("A{$rowNum}:A" . ($rowNum + 1))->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

$sheet->getStyle("F{$rowNum}:G" . ($rowNum + 1))->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

// Merge subtitle cells
$sheet->mergeCells("B{$rowNum}:D{$rowNum}");
$sheet->mergeCells("H{$rowNum}:H{$rowNum}");
$sheet->mergeCells('B30:D31');
$sheet->mergeCells('H30:H31');

$rowNum++;

// Subtitle row
$sheet->mergeCells("B{$rowNum}:D{$rowNum}");
$sheet->mergeCells("H{$rowNum}:H{$rowNum}");

$sheet->setCellValue("B{$rowNum}", '( Name & Signature of Employee )');
$sheet->setCellValue("H{$rowNum}", '( Name & Signature of Immediate Supervisor )');

$sheet->getStyle("B{$rowNum}:D{$rowNum}")->applyFromArray([
    'font' => [
        'italic' => true,
        'size' => 10
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

$sheet->getStyle("F{$rowNum}:H{$rowNum}")->applyFromArray([
    'font' => [
        'italic' => true,
        'size' => 10
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
]);

$filename = 'DTR_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $emp['last_name'] . '_' . $emp['first_name']) . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;