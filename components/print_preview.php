<?php
include '../backend/profile.php';

$attendanceRows = [];
if ($attendance_history && $attendance_history->num_rows > 0) {
    mysqli_data_seek($attendance_history, 0);
    while ($row = $attendance_history->fetch_assoc()) {
        $attendanceRows[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Preview - PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../assets/print_preview.css">
</head>
<body>

<div class="preview-page">
    <div class="preview-actions no-print">
        <a href="../pages/profile.php?id=<?= $id ?>&start_date=<?= urlencode($_GET['start_date'] ?? '') ?>&end_date=<?= urlencode($_GET['end_date'] ?? '') ?>" class="btn-back-preview">Back</a>
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
                <td><strong>NAME:</strong></td>
                <td style="text-align: center"><?= htmlspecialchars(strtoupper($emp['last_name'] . ', ' . $emp['first_name'])) ?></td>
            </tr>
            <tr>
                <td><strong>DESIGNATION:</strong></td>
                <td style="text-align: center"><?= htmlspecialchars(strtoupper($emp['position'])) ?></td>
            </tr>
            <tr>
                <td><strong>BRANCH:</strong></td>
                <td><input type="text" value="<?= htmlspecialchars(strtoupper($emp['branch'] ?? 'MAIN')) ?>" class="print-input"></td>
            </tr>
            <tr>
                <td><strong>CUT-OFF PERIOD:</strong></td>
                <td>
                    <input type="text"
                           value="<?=
                           !empty($_GET['start_date']) && !empty($_GET['end_date'])
                               ? htmlspecialchars(strtoupper(date('F d, Y', strtotime($_GET['start_date'])) . ' - ' . date('F d, Y', strtotime($_GET['end_date']))))
                               : 'ALL RECORDS'
                           ?>"
                           class="print-input">
                </td>
            </tr>
        </table>

        <table class="dtr-sheet">
            <thead>
                <tr>
                    <th rowspan="2">DAY</th>
                    <th rowspan="2">DATE</th>
                    <th rowspan="2">TIME IN</th>
                    <th rowspan="2">TIME OUT</th>
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
                $dayNo = 1;
                $daysRendered = 0;
                $totalOtHours = 0;

                if (!empty($attendanceRows)):
                    foreach ($attendanceRows as $row):
                        $timeIn  = !empty($row['time_in']) ? date('H:i', strtotime($row['time_in'])) : '';
                        $timeOut = !empty($row['time_out']) ? date('H:i', strtotime($row['time_out'])) : '';

                        $otFrom = '';
                        $otTo   = '';
                        $otHours = '';

                        if (!empty($row['time_in']) || !empty($row['time_out'])) {
                            $daysRendered += 1;
                        }
                ?>
                    <tr>
                        <td><?= $dayNo++ ?></td>
                        <td><?= date('m/d/Y', strtotime($row['attendance_date'])) ?></td>
                        <td><?= !empty($row['time_in']) ? date('H:i', strtotime($row['time_in'])) : '' ?></td>
                        <td><?= !empty($row['time_out']) ? date('H:i', strtotime($row['time_out'])) : '' ?></td>
                        <td><input type="time" class="print-cell-input"></td>
                        <td><input type="time" class="print-cell-input"></td>
                        <td><input type="number" step="0.01" class="print-cell-input print-hours-input"></td>
                        <td><input type="text" class="print-cell-input" value="<?= !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : '' ?>"></td>
                    </tr>
                <?php
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">NO RECORDS FOUND</td>
                    </tr>
                <?php endif; ?>

                <?php for ($i = $dayNo; $i <= 16; $i++): ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><input type="time" class="print-cell-input"></td>
                        <td><input type="time" class="print-cell-input"></td>
                        <td><input type="number" step="0.01" class="print-cell-input print-hours-input"></td>
                        <td><input type="text" class="print-cell-input"></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>TOTAL NO. OF DAYS RENDERED</strong></td>
                    <td colspan="2"><?= $daysRendered ?></td>
                    <td colspan="2" style="table-layout: fixed;"><strong>TOTAL OT HOURS:</strong></td>
                    <td colspan="2"><input type="number" step="0.01" class="print-cell-input print-hours-input"></td>
                </tr>
            </tfoot>
        </table>

        <div class="dtr-certify">
            <textarea class="print-note">THIS IS TO CERTIFY THAT THE ABOVE INFORMATION IS CORRECT.</textarea>
        </div>

        <div class="dtr-signatures">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div><?= htmlspecialchars(strtoupper($emp['last_name'] . ', ' . $emp['first_name'])) ?></div>
                <small>( Name & Signature of Employee )</small>
            </div>

            <div class="sig-box">
                <div class="sig-line"></div>
                <input type="text" class="print-signature-input" value="IMMEDIATE SUPERVISOR">
                <small>( Name & Signature of Immediate Supervisor )</small>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/print_preview.js"></script>
</body>
</html>