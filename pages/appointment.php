<?php
require_once '../config/session.php';
require_once '../auth/db_connect.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

// Check for expired leaves and update employee status back to active
$today = date('Y-m-d');
$expired_leaves = $conn->query("
    SELECT lr.employee_id
    FROM leave_requests lr
    WHERE lr.status = 'Approved'
    AND lr.end_date < '$today'
    AND EXISTS (
        SELECT 1 FROM employees e
        WHERE e.id = lr.employee_id
        AND e.status = 'on leave'
    )
");

while ($leave = $expired_leaves->fetch_assoc()) {
    $conn->query("UPDATE employees SET status = 'active', updated_at = NOW() WHERE id = " . $leave['employee_id']);
}

// Fetch all leave requests with employee details
$query = $conn->query("
    SELECT lr.*, e.first_name, e.last_name, e.employee_code,
           approver.first_name as approver_first, approver.last_name as approver_last
    FROM leave_requests lr
    JOIN employees e ON lr.employee_id = e.id
    LEFT JOIN employees approver ON lr.approved_by = approver.id
    ORDER BY lr.created_at DESC
");
$leave_requests = $query->fetch_all(MYSQLI_ASSOC);

// Get leave statistics
$stats = $conn->query("
    SELECT
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM leave_requests
")->fetch_assoc();

// Get employees for dropdown
$employees = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status = 'active' ORDER BY last_name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHealth Clinic - Leave Requests</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/appointment.css">
</head>

<body>
    <div class="dashboard">
        <?php include '../components/sidebar.php'; ?>
        <div class="main">
            <?php include '../components/header.php'; ?>

            <div class="main-layer">
                <div class="app-header">
                    <div class="header-text">
                        <h1><i class="bi bi-calendar-x" style="color: #1a7318;"></i> Leave Requests</h1>
                        <p>Manage employee leave requests and approvals.</p>
                    </div>
                    <button class="btn-add-appointment">
                        <i class="bi bi-plus-lg"></i> New Leave Request
                    </button>
                </div>

                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-label">Total Requests</span>
                        <span class="stat-value"><?php echo $stats['total_requests'] ?? 0; ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Pending</span>
                        <span class="stat-value" style="color: #f59e0b;"><?php echo $stats['pending'] ?? 0; ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Approved</span>
                        <span class="stat-value" style="color: #1a7318;"><?php echo $stats['approved'] ?? 0; ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Rejected</span>
                        <span class="stat-value" style="color: #dc2626;"><?php echo $stats['rejected'] ?? 0; ?></span>
                    </div>
                </div>

                <div class="table-card">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($leave_requests) > 0): ?>
                                <?php foreach ($leave_requests as $leave): ?>
                                    <tr>
                                        <td>
                                            <div class="client-info">
                                                <div class="name-circle"><?php echo strtoupper(substr($leave['first_name'], 0, 1) . substr($leave['last_name'], 0, 1)); ?></div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></strong>
                                                    <small><?php echo htmlspecialchars($leave['employee_code']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="service-tag"><?php echo htmlspecialchars($leave['leave_type']); ?></span></td>
                                        <td>
                                            <div class="datetime">
                                                <span><?php echo date('M d, Y', strtotime($leave['start_date'])); ?> - <?php echo date('M d, Y', strtotime($leave['end_date'])); ?></span>
                                                <small><?php
                                                        $start = new DateTime($leave['start_date']);
                                                        $end = new DateTime($leave['end_date']);
                                                        $days = $start->diff($end)->days + 1;
                                                        echo $days . ' day' . ($days > 1 ? 's' : '');
                                                        ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <button
                                                class="btn-reason"
                                                onclick="showReason(`<?= htmlspecialchars(addslashes($leave['reason'])) ?>`)">
                                                View
                                            </button>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($leave['status']); ?>">
                                                <?php echo htmlspecialchars($leave['status']); ?>
                                                <?php if ($leave['status'] !== 'Pending' && $leave['approver_first']): ?>
                                                    <br><small>by <?php echo htmlspecialchars($leave['approver_first'] . ' ' . $leave['approver_last']); ?></small>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($leave['created_at'])); ?></td>
                                        <td style="text-align:right; display:flex; gap:6px; justify-content:flex-end;">

                                            <?php if ($leave['status'] === 'Pending'): ?>
                                                <a href="../backend/leave_request.php?action=approve&id=<?= $leave['id']; ?>"
                                                    class="btn-action approve">
                                                    Approve
                                                </a>

                                                <a href="../backend/leave_request.php?action=reject&id=<?= $leave['id']; ?>"
                                                    class="btn-action reject">
                                                    Reject
                                                </a>
                                            <?php endif; ?>

                                            <a href="../backend/leave_request.php?delete=<?= $leave['id']; ?>"
                                                class="btn-action delete">
                                                Delete
                                            </a>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No leave requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Reason Modal -->
                <div id="reasonModal" class="reason-modal">
                    <div class="reason-modal-content">
                        <span class="reason-close" onclick="closeReasonModal()">&times;</span>
                        <h3>Leave Reason</h3>
                        <p id="reasonText"></p>
                    </div>
                </div>

                <!-- Leave Request Modal -->
                <div id="leaveModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>New Leave Request</h2>
                        <form method="POST" action="../backend/leave_request.php">
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php
                                    $employees->data_seek(0); // Reset result pointer
                                    while ($emp = $employees->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $emp['id']; ?>">
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_code'] . ')'); ?>
                                            <?php
                                            // Get current employee status
                                            $status_query = $conn->query("SELECT status FROM employees WHERE id = " . $emp['id']);
                                            $status_result = $status_query->fetch_assoc();
                                            $current_status = $status_result['status'];
                                            if ($current_status === 'on leave') {
                                                echo ' - ON LEAVE';
                                            }
                                            ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Leave Type</label>
                                <select name="leave_type" required>
                                    <option value="">Select Leave Type</option>
                                    <option value="Vacation">Vacation Leave</option>
                                    <option value="Sick">Sick Leave</option>
                                    <option value="Personal">Personal Leave</option>
                                    <option value="Maternity">Maternity Leave</option>
                                    <option value="Paternity">Paternity Leave</option>
                                    <option value="Emergency">Emergency Leave</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="text" id="startDatePicker" name="start_date" placeholder="Select start date" required>
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="text" id="endDatePicker" name="end_date" placeholder="Select end date" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Reason</label>
                                <textarea name="reason" rows="3" placeholder="Please provide a reason for your leave request..." required></textarea>
                            </div>
                            <div class="modal-actions">
                                <button type="submit" class="btn-add-appointment">
                                    <i class="bi bi-send"></i> Submit Request
                                </button>
                                <button type="button" class="close-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../components/notif.php'; ?>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/actions.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById("leaveModal");
            const openBtn = document.querySelector(".btn-add-appointment");
            const closeBtn = modal.querySelector(".close");
            const cancelBtn = modal.querySelector(".close-btn");

            // Initialize date pickers
            const startPicker = flatpickr("#startDatePicker", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                minDate: "today",
                disableMobile: "true",
                static: true,
                onReady: function(selectedDates, dateStr, instance) {
                    const calendar = instance.calendarContainer;
                    calendar.style.borderRadius = "12px";
                    calendar.style.boxShadow = "0 10px 25px rgba(0,0,0,0.1)";
                    calendar.style.zIndex = "9999";
                }
            });

            const endPicker = flatpickr("#endDatePicker", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                minDate: "today",
                disableMobile: "true",
                static: true,
                onReady: function(selectedDates, dateStr, instance) {
                    const calendar = instance.calendarContainer;
                    calendar.style.borderRadius = "12px";
                    calendar.style.boxShadow = "0 10px 25px rgba(0,0,0,0.1)";
                    calendar.style.zIndex = "9999";
                }
            });

            // Link date pickers so end date can't be before start date
            startPicker.config.onChange.push(function(selectedDates, dateStr) {
                endPicker.set('minDate', dateStr);
            });

            // Modal Logic
            const closeModal = () => {
                modal.classList.remove("show");
                setTimeout(() => {
                    modal.style.display = "none";
                }, 300);
            };

            // Open modal
            openBtn.addEventListener("click", function(e) {
                e.preventDefault();
                modal.style.display = "block";
                setTimeout(() => {
                    modal.classList.add("show");
                }, 10);
            });

            // Close modal events
            closeBtn.addEventListener("click", closeModal);
            cancelBtn.addEventListener("click", closeModal);

            // Close when clicking outside modal
            window.addEventListener("click", (e) => {
                if (e.target === modal) closeModal();
            });
        });

        function showReason(reason) {
            document.getElementById("reasonText").innerText = reason;
            const modal = document.getElementById("reasonModal");

            modal.style.display = "flex";
            setTimeout(() => modal.classList.add("show"), 10);
        }

        function closeReasonModal() {
            const modal = document.getElementById("reasonModal");
            modal.classList.remove("show");
            setTimeout(() => modal.style.display = "none", 300);
        }
    </script>
</body>

</html>