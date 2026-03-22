<?php
require_once '../config/session.php';
require_once '../auth/db_connect.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}
// include '../backend/appointment_logic.php'; 
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
    <link rel="stylesheet" href="../assets/user_dashboard_header.css">
    <link rel="stylesheet" href="../assets/appointment.css">
</head>

<body>
    <div class="dashboard">
        <div class="main">
            <?php include '../components/user_dashboard_header.php'; ?>

            <div class="main-layer">
                <div class="app-header">
                    <div class="header-text">
                        <h1><i class="bi bi-journal-plus" style="color: #1a7318;"></i> Appointments</h1>
                        <p>Manage client bookings and service schedules.</p>
                    </div>
                    <button class="btn-add-appointment" onclick="openModal()">
                        <i class="bi bi-plus-lg"></i> New Appoointment
                    </button>
                </div>

                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-label">Total Today</span>
                        <span class="stat-value">12</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Pending</span>
                        <span class="stat-value" style="color: #f59e0b;">5</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Confirmed</span>
                        <span class="stat-value" style="color: #1a7318;">7</span>
                    </div>
                </div>

                <div class="table-card">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Specialist</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="client-info">
                                        <div class="name-circle">JD</div>
                                        <div>
                                            <strong>Jane Doe</strong>
                                            <small>+63 912 345 6789</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="service-tag">Facial Treatment</span></td>
                                <td>
                                    <div class="datetime">
                                        <span>Oct 24, 2025</span>
                                        <small>10:30 AM</small>
                                    </div>
                                </td>
                                <td>Dr. Smith</td>
                                <td><span class="badge badge-confirmed">Confirmed</span></td>
                                <td style="text-align:right;">
                                    <button class="btn-icon"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn-icon delete"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>