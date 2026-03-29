<?php
require_once '../config/session.php';
require_once '../auth/db_connect.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

date_default_timezone_set('Asia/Manila');

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Basic guard
if ($month < 1 || $month > 12) {
    $month = (int)date('n');
}
if ($year < 1970 || $year > 2100) {
    $year = (int)date('Y');
}

$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$number_days = (int)date('t', $first_day_of_month);
$date_info = getdate($first_day_of_month);
$day_of_week = (int)$date_info['wday'];

$prev_month = ($month == 1) ? 12 : $month - 1;
$prev_year  = ($month == 1) ? $year - 1 : $year;
$next_month = ($month == 12) ? 1 : $month + 1;
$next_year  = ($month == 12) ? $year + 1 : $year;

$holidays = [];
$holiday_error = false;

$api_url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/PH";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 8,
        'header' => "User-Agent: PrimeHealthCalendar/1.0\r\n"
    ]
]);

$response = @file_get_contents($api_url, false, $context);

if ($response !== false) {
    $decoded = json_decode($response, true);

    if (is_array($decoded)) {
        foreach ($decoded as $holiday) {
            if (!empty($holiday['date'])) {
                $holiday_date = $holiday['date'];

                $holidays[$holiday_date] = [
                    'localName' => $holiday['localName'] ?? 'Holiday',
                    'name'      => $holiday['name'] ?? 'Holiday',
                    'types'     => $holiday['types'] ?? []
                ];
            }
        }
    } else {
        $holiday_error = true;
    }
} else {
    $holiday_error = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
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

                <?php if ($holiday_error): ?>
                    <div class="calendar-warning">
                        Holiday data could not be loaded right now. The calendar still works, but holiday labels may be missing.
                    </div>
                <?php endif; ?>

                <div class="calendar-card">
                    <div class="calendar-scroll">
                        <div class="calendar-grid">
                            <?php
                            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                            foreach ($days as $d) {
                                echo "<div class='day-label'>{$d}</div>";
                            }

                            for ($i = 0; $i < $day_of_week; $i++) {
                                echo "<div class='calendar-cell empty'></div>";
                            }

                            for ($day = 1; $day <= $number_days; $day++) {
                                $full_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $is_today = ($full_date === date('Y-m-d'));
                                $is_holiday = isset($holidays[$full_date]);

                                $cell_classes = 'calendar-cell';
                                if ($is_today) {
                                    $cell_classes .= ' is-today';
                                }
                                if ($is_holiday) {
                                    $cell_classes .= ' is-holiday';
                                }

                                echo "<div class='{$cell_classes}'>";
                                echo "<div>";
                                echo "<span class='day-num'>{$day}</span>";

                                if ($is_today) {
                                    echo "<span class='today-badge'>TODAY</span>";
                                }

                                if ($is_holiday) {
                                    $holiday_name = htmlspecialchars($holidays[$full_date]['name']);
                                    echo "<span class='holiday-label'>{$holiday_name}</span>";

                                    if (!empty($holidays[$full_date]['types'][0])) {
                                        $holiday_type = htmlspecialchars($holidays[$full_date]['types'][0]);
                                        echo "<span class='holiday-type'>{$holiday_type}</span>";
                                    }
                                }

                                echo "</div>";

                                echo "<div class='status-dots'>";
                                if ($is_holiday) {
                                    echo "<div class='dot dot-holiday' title='Holiday'></div>";
                                } else {
                                    echo "<div class='dot dot-present' title='Normal day'></div>";
                                }
                                echo "</div>";

                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
</body>
</html>