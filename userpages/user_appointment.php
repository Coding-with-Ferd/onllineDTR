<?php
require_once '../config/session.php';
require_once '../auth/db_connect.php';

if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

$userEmail = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT id, first_name, last_name, position FROM employees WHERE email = ? OR employee_code = ?");
$stmt->bind_param("ss", $userEmail, $userEmail);
$stmt->execute();
$employeeResult = $stmt->get_result();

if ($employeeResult->num_rows === 0) {
    die("No employee profile found for this account.");
}

$employee = $employeeResult->fetch_assoc();
$emp_id = $employee['id'];
$emp_name = $employee['first_name'] . ' ' . $employee['last_name'];
$emp_position = $employee['position'];

$query = $conn->query("
    SELECT lr.*, e.first_name, e.last_name,
           approver.first_name as approver_first, approver.last_name as approver_last
    FROM leave_requests lr
    JOIN employees e ON lr.employee_id = e.id
    LEFT JOIN employees approver ON lr.approved_by = approver.id
    WHERE lr.employee_id = {$emp_id}
    ORDER BY lr.created_at DESC
");
$leave_requests = $query->fetch_all(MYSQLI_ASSOC);

$stats = $conn->query("
    SELECT
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM leave_requests
    WHERE employee_id = {$emp_id}
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHealth Clinic - My Leave Requests</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/user_dashboard_header.css">
    <link rel="stylesheet" href="../assets/user_appointment.css">
</head>

<body>
    <div class="dashboard">
        <div class="main">
            <?php include '../components/user_dashboard_header.php'; ?>

            <div class="main-layer">
                <div class="app-header">
                    <div class="header-text">
                        <h1><i class="bi bi-calendar-x" style="color: #1a7318;"></i> My Leave Requests</h1>
                        <p>View and manage your leave requests.</p>
                    </div>
                    <button class="btn-add-appointment" onclick="openModal()">
                        <i class="bi bi-plus-lg"></i> Request Leave
                    </button>
                </div>

                <div class="table-card">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($leave_requests) > 0): ?>
                                <?php foreach ($leave_requests as $leave): ?>
                                    <tr>
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
                                        <td style="text-align:right;">
                                            <?php if ($leave['status'] === 'Pending'): ?>
                                                <a href="#"
                                                    class="btn-action cancel"
                                                    title="Cancel Request"
                                                    onclick="confirmCancel(<?php echo $leave['id']; ?>)">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">No leave requests found.</td>
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
                        <h2>Request Leave</h2>
                        <form method="POST" action="../backend/user_leave.php">
                            <input type="hidden" name="employee_id" value="<?php echo $emp_id; ?>">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <?php include '../components/notif.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById("leaveModal");
            const openBtn = document.querySelector(".btn-add-appointment");
            const closeBtn = modal.querySelector(".close");
            const cancelBtn = modal.querySelector(".close-btn");

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
                altFormat: "Y-m-d",
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

        function confirmCancel(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to cancel this leave request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1a6d18',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../backend/user_leave.php?cancel=" + id;
                }
            });
        }
    </script>
</body>

</html>