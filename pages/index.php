<?php
require_once '../config/session.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon-32x32.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/dashboard.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard">

        <?php include '../components/sidebar.php'; ?>

        <div class="main">

            <?php include '../components/header.php'; ?>

            <div class="main-layer">
                <div class="content">
                    <div class="dashboard-text">
                        <h1>Dashboard</h1>
                        <p>Welcome to PrimeHealth Clinic Dashboard.</p>
                    </div>

                    <div class="dashboard-cards">

                        <div class="card">
                            <h3>Total Employees</h3>
                            <p id="totalEmployees">...</p>
                        </div>

                        <div class="card">
                            <h3>Present Today</h3>
                            <p id="presentToday">...</p>
                        </div>

                        <div class="card">
                            <h3>Absent Today</h3>
                            <p id="absentToday">...</p>
                        </div>

                    </div>

                    <div class="chart-container">
                        <h2>Attendance Trends (Last 7 Days)</h2>

                        <div class="chart-box">
                            <canvas id="attendanceChart"></canvas>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/chart.js"></script>
</body>

</html>