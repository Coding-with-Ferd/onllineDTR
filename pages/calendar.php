<?php
require_once '../config/session.php';
require_once '../auth/db_connect.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

date_default_timezone_set('Asia/Manila');

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$number_days = date('t', $first_day_of_month);
$date_info = getdate($first_day_of_month);
$day_of_week = $date_info['wday'];

$prev_month = ($month == 1) ? 12 : $month - 1;
$prev_year = ($month == 1) ? $year - 1 : $year;
$next_month = ($month == 12) ? 1 : $month + 1;
$next_year = ($month == 12) ? $year + 1 : $year;
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
    <link rel="stylesheet" href="../assets/calendar.css">

</head>

<body>
    <div class="dashboard">
        <?php include '../components/sidebar.php'; ?>
        <div class="main">
            <?php include '../components/header.php'; ?>

            <div class="main-layer">
                <div class="calendar-header-section">
                    <div class="calendar-title-info">
                        <h1>Operations Calendar</h1>
                        <p>Managing aesthetic clinic schedules and staff attendance records.</p>
                    </div>

                    <div class="calendar-controls">
                        <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>" class="nav-btn">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <div class="current-month-display">
                            <?= date('F Y', $first_day_of_month) ?>
                        </div>
                        <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>" class="nav-btn">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="calendar-card">
                    <div class="calendar-grid">
                        <?php
                        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        foreach ($days as $d) echo "<div class='day-label'>$d</div>";

                        // Padding for previous month
                        for ($i = 0; $i < $day_of_week; $i++) {
                            echo "<div class='calendar-cell empty'></div>";
                        }

                        // Days of the Month
                        for ($day = 1; $day <= $number_days; $day++) {
                            $full_date = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-" . str_pad($day, 2, "0", STR_PAD_LEFT);
                            $is_today = ($full_date == date('Y-m-d'));
                            $today_class = $is_today ? 'is-today' : '';

                            echo "<div class='calendar-cell $today_class'>";
                            echo "<div>";
                            echo "<span class='day-num'>$day</span>";
                            if ($is_today) echo "<span class='today-badge'>TODAY</span>";
                            echo "</div>";

                            // Placeholder for dots (You can integrate your SQL here)
                            echo "<div class='status-dots'>
                                        <div class='dot dot-present'></div>
                                        <div class='dot dot-present'></div>
                                      </div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>