<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// include '../backend/branch_logic.php'; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Status - Naked Beauty Aesthetics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <style>
        .main-layer {
            padding: 30px;
            background-color: var(--bg-light);
        }

        .branch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .branch-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .branch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        .branch-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .branch-info h3 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .branch-info p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 5px 0 0 0;
        }

        /* Status Pill */
        .status-indicator {
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-open {
            background: #dcfce7;
            color: #166534;
        }

        .status-closed {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Metrics Row */
        .branch-metrics {
            display: flex;
            background: #f8fafc;
            border-radius: 16px;
            padding: 15px;
            margin: 20px 0;
            gap: 15px;
        }

        .metric-item {
            flex: 1;
            text-align: center;
        }

        .metric-value {
            display: block;
            font-size: 1.2rem;
            font-weight: 800;
            color: #1e293b;
        }

        .metric-label {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
        }

        /* Toggle Switch */
        .switch-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
        }

        .switch-label {
            font-weight: 600;
            font-size: 14px;
            color: #475569;
        }

        /* Pro Toggle Button */
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #1a7318;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <?php include '../components/sidebar.php'; ?>
        <div class="main">
            <?php include '../components/header.php'; ?>

            <div class="main-layer">
                <div style="margin-bottom: 30px;">
                    <h1 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin:0;">
                        <i class="bi bi-geo-alt-fill" style="color: #1a7318;"></i> Branch Status
                    </h1>
                    <p style="color: #64748b; margin-top: 5px;">Live monitoring of clinic operations and availability.</p>
                </div>

                <div class="branch-grid">
                    <div class="branch-card">
                        <div class="branch-header">
                            <div class="branch-info">
                                <h3>Camarin Branch</h3>
                                <p><i class="bi bi-clock"></i> 9:00 AM - 6:00 PM</p>
                            </div>
                            <span class="status-indicator status-open">Open</span>
                        </div>

                        <div class="branch-metrics">
                            <div class="metric-item">
                                <span class="metric-value">08</span>
                                <span class="metric-label">Staff On-Duty</span>
                            </div>
                            <div class="metric-item" style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                                <span class="metric-value">14</span>
                                <span class="metric-label">Appts Today</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-value">02</span>
                                <span class="metric-label">Waiting</span>
                            </div>
                        </div>

                        <div class="switch-container">
                            <span class="switch-label">Operation Status</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="branch-card">
                        <div class="branch-header">
                            <div class="branch-info">
                                <h3>Brixton Branch</h3>
                                <p><i class="bi bi-clock"></i> 9:00 AM - 6:00 PM</p>
                            </div>
                            <span class="status-indicator status-closed">Closed</span>
                        </div>

                        <div class="branch-metrics">
                            <div class="metric-item">
                                <span class="metric-value">0</span>
                                <span class="metric-label">Staff On-Duty</span>
                            </div>
                            <div class="metric-item" style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                                <span class="metric-value">0</span>
                                <span class="metric-label">Appts Today</span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-value">0</span>
                                <span class="metric-label">Waiting</span>
                            </div>
                        </div>

                        <div class="switch-container">
                            <span class="switch-label" style="color: #ef4444;">Closed for Maintenance</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>