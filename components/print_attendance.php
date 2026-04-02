<?php
include '../backend/attendance.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Preview - Attendance Records</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../assets/print_preview.css">
</head>
<body>

<div class="preview-page">
    <div class="preview-actions no-print">
        <a href="../pages/attendance_list.php?start_date=<?= urlencode($_GET['start_date'] ?? '') ?>&end_date=<?= urlencode($_GET['end_date'] ?? '') ?>" class="btn-back-preview">
            Back
        </a>
        <button type="button" class="btn-print-preview" onclick="window.print()">Print Now</button>
    </div>

    <div class="print-dtr-wrap">
        <div class="dtr-header">
            <div class="logo-title">
                <img src="../assets/images/login.png" alt="PrimeHealth Logo" class="dtr-logo">
                <div class="dtr-title-wrap">
                    <h1>PRIMEHEALTH CLINIC</h1>
                    <p>Service Beyond Health</p>
                </div>
            </div>
        </div>

        <table class="dtr-meta">
            <tr>
                <td><strong>REPORT:</strong></td>
                <td style="text-align: center;">ALL ATTENDANCE RECORDS</td>
            </tr>
            <tr>
                <td><strong>DATE RANGE:</strong></td>
                <td>
                    <input
                        type="text"
                        value="<?=
                            (!empty($_GET['start_date']) && !empty($_GET['end_date']))
                                ? htmlspecialchars(strtoupper(date('F d, Y', strtotime($_GET['start_date'])) . ' - ' . date('F d, Y', strtotime($_GET['end_date']))))
                                : 'ALL RECORDS'
                        ?>"
                        class="print-input"
                    >
                </td>
            </tr>
            <tr>
                <td><strong>BRANCH:</strong></td>
                <td><input type="text" value="ALL BRANCHES" class="print-input"></td>
            </tr>
        </table>

        <table class="dtr-sheet">
            <thead>
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">EMPLOYEE</th>
                    <th rowspan="2">DATE</th>
                    <th rowspan="2">TIME IN</th>
                    <th rowspan="2">TIME OUT</th>
                    <th rowspan="2">TOTAL HRS</th>
                    <th rowspan="2">STATUS</th>
                    <th colspan="2">OVERTIME</th>
                    <th rowspan="2">OT HOURS</th>
                    <th rowspan="2">REMARKS</th>
                </tr>
                <tr>
                    <th>FROM</th>
                    <th>TO</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rowNo = 1;

                foreach ($employees as $emp):
                    $fullName = htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']);

                    if (isset($attendance_records[$emp['id']])):
                        foreach ($attendance_records[$emp['id']] as $rec_date => $record):
                            $t_in = $record['time_in'] ?? null;
                            $t_out = $record['time_out'] ?? null;
                            $stat = $record['status'] ?? 'Present';
                            $hrs = totalHours($t_in, $t_out, $stat);
                ?>
                    <tr>
                        <td><?= $rowNo++ ?></td>
                        <td><?= $fullName ?></td>
                        <td><?= date('m/d/Y', strtotime($rec_date)) ?></td>
                        <td><?= formatTime($t_in) ?></td>
                        <td><?= formatTime($t_out) ?></td>
                        <td><?= is_numeric($hrs) ? number_format($hrs, 2) : '-' ?></td>
                        <td><?= htmlspecialchars($stat) ?></td>
                        <td><input type="time" class="print-cell-input"></td>
                        <td><input type="time" class="print-cell-input"></td>
                        <td><input type="text" class="print-cell-input print-hours-input" placeholder="0.00"></td>
                        <td>
                            <input
                                type="text"
                                class="print-cell-input"
                                value="<?= !empty($record['remarks']) ? htmlspecialchars($record['remarks']) : '' ?>"
                            >
                        </td>
                    </tr>
                <?php
                        endforeach;
                    endif;
                endforeach;
                ?>

                <?php if ($rowNo === 1): ?>
                    <tr>
                        <td colspan="11" style="text-align:center;">NO RECORDS FOUND</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8"><strong>TOTAL OT HOURS</strong></td>
                    <td colspan="3" id="totalOtHours">0.00</td>
                </tr>
            </tfoot>
        </table>

        <div class="dtr-certify">
            <textarea class="print-note">THIS IS TO CERTIFY THAT THE ABOVE INFORMATION IS CORRECT.</textarea>
        </div>

        <div class="dtr-signatures">
            <div class="sig-box">
                <div class="sig-line"></div>
                <input type="text" class="print-signature-input" value="PREPARED BY">
                <small>( Name & Signature )</small>
            </div>

            <div class="sig-box">
                <div class="sig-line"></div>
                <input type="text" class="print-signature-input" value="APPROVED BY">
                <small>( Name & Signature )</small>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/print_preview.js"></script>
</body>
</html>