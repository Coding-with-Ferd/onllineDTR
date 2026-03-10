<?php
session_start();
include '../auth/db_connect.php';

// Get date from GET or use today
$date = $_GET['date'] ?? date('Y-m-d');

// Fetch all employees
$query = $conn->query("SELECT * FROM employees ORDER BY last_name ASC");
$employees = $query->fetch_all(MYSQLI_ASSOC);

// Fetch existing daily status for the selected date
$status_records = [];
$status_query = $conn->prepare("SELECT * FROM daily_status WHERE status_date = ?");
$status_query->bind_param("s", $date);
$status_query->execute();
$res = $status_query->get_result();
while($row = $res->fetch_assoc()){
    $status_records[$row['employee_id']] = $row['status'];
}

// Status options
$status_options = ['Regular working day', 'Absent', 'Half day', 'Special non-working holiday', 'Holiday', 'Leave'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Status - PrimeHealth</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">

    <style>
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .status-table th, .status-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .status-table th {
            background-color: #1a6d18;
            color: #fff;
        }

        .status-table tr:hover {
            background-color: rgba(0,0,0,0.05);
        }

        select.status-select {
            padding: 5px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .save-btn {
            background-color: #1a6d18;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
        }

        .save-btn:hover {
            opacity: 0.9;
        }

        .date-picker {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="dashboard">

    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>

    <div class="main">

        <!-- Header -->
        <?php include '../components/header.php'; ?>

        <div class="main-layer">
            <div class="content">
                <h1>Daily Status / Exception</h1>

                <form action="../backend/daily_status.php" method="POST">
                    <label for="status_date">Select Date:</label>
                    <input type="date" id="status_date" name="status_date" class="date-picker" value="<?= $date ?>">

                    <table class="status-table">
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Daily Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($employees as $emp): ?>
                            <tr>
                                <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                                <td><?= htmlspecialchars($emp['position']) ?></td>
                                <td>
                                    <select name="status[<?= $emp['id'] ?>]" class="status-select">
                                        <?php foreach($status_options as $option): ?>
                                            <option value="<?= $option ?>" <?= (isset($status_records[$emp['id']]) && $status_records[$emp['id']] == $option) ? 'selected' : '' ?>><?= $option ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="submit" class="save-btn"><i class="bi bi-save"></i> Save Status</button>
                </form>
            </div>
        </div>

    </div>
</div>
</body>
</html>