<?php
require_once '../config/session.php';

if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

// Pagination setup
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 9;
$offset = ($currentPage - 1) * $perPage;

// Total employees count
$totalQuery = $conn->query("SELECT COUNT(*) as total FROM employees");
$totalEmployees = $totalQuery->fetch_assoc()['total'] ?? 0;

// Fetch current page employees
$query = $conn->query("SELECT * FROM employees ORDER BY last_name ASC LIMIT $perPage OFFSET $offset");
$employees = $query->fetch_all(MYSQLI_ASSOC);

$pagination = [
    'current_page' => $currentPage,
    'total_items' => $totalEmployees,
    'per_page' => $perPage,
    'base_url' => 'employees.php',
    'query_params' => $_GET,
];

$branches = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/addemployee.css">
    <link rel="stylesheet" href="../assets/employees.css">

</head>

<body>
    <div class="dashboard">

        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <div class="main">

            <!-- Header -->
            <?php include '../components/header.php'; ?>

            <!-- Main content -->
            <div class="main-layer">
                    <div class="employee-header">
                        <div class="employee-text">
                            <h1>Employees</h1>
                            <p>Manage employee records.</p>
                        </div>
                        <a href="add_employee.php" class="add-btn"><i class="bi bi-plus-lg"></i> Add Employee</a>
                    </div>
                    <div class="table-container">
                        <table class="employee-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Position</th>
                                    <th>Type</th>
                                    <th>Contact Info</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($employees) > 0): ?>
                                    <?php foreach ($employees as $emp): ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-avatar"><?= strtoupper(substr($emp['first_name'], 0, 1)) ?></div>
                                                    <div class="user-details">
                                                        <a href="profile.php?id=<?= $emp['id'] ?>" class="user-employee">
                                                            <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                                        </a>
                                                        <span class="user-code"><?= htmlspecialchars($emp['employee_code']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($emp['position']) ?></td>
                                            <td><span class="type-tag"><?= ucfirst($emp['position_type']) ?></span></td>
                                            <td>
                                                <div class="contact-cell">
                                                    <span style="text-transform: lowercase;"><i class="bi bi-envelope"></i> <?= htmlspecialchars($emp['email']) ?></span>
                                                    <span><i class="bi bi-telephone"></i> <?= htmlspecialchars($emp['phone']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-pill <?= str_replace(' ', '-', strtolower($emp['status'])) ?>">
                                                    <?= ucfirst($emp['status']) ?>
                                                </span>
                                            </td>
                                            <td class="table-action">
                                                <button
                                                    type="button"
                                                    class="edit-icon edit-btn"
                                                    data-id="<?= $emp['id'] ?>"
                                                    data-first-name="<?= htmlspecialchars($emp['first_name']) ?>"
                                                    data-middle-name="<?= htmlspecialchars($emp['middle_name']) ?>"
                                                    data-last-name="<?= htmlspecialchars($emp['last_name']) ?>"
                                                    data-position="<?= htmlspecialchars($emp['position']) ?>"
                                                    data-position-type="<?= $emp['position_type'] ?>"
                                                    data-email="<?= htmlspecialchars($emp['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($emp['phone']) ?>"
                                                    data-hire-date="<?= $emp['hire_date'] ?>"
                                                    data-branch-id="<?= htmlspecialchars($emp['branch_id'] ?? '') ?>"
                                                    data-status="<?= $emp['status'] ?>"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <a href="../backend/employee.php?id=<?= $emp['id'] ?>" class="delete-icon delete-confirm" title="Delete"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">No employees found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php include '../components/pagination.php'; ?>


                <!-- Add Employee Modal -->
                <div id="addEmployeeModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Add Employee</h2>
                        <form method="POST" action="../backend/employee.php">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" required>
                            </div>
                            <div class="form-group">
                                <label>Hire Date</label>
                                <input type="text" id="modernDatePicker" name="hire_date" placeholder="Select Date.." required>
                            </div>
                            <div class="form-group">
                                <label>Branch</label>
                                <select name="branch_id" required>
                                    <option value="">Select Branch</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?= $branch['id'] ?>">
                                            <?= htmlspecialchars($branch['branch_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Position</label>
                                <input type="text" name="position" required>
                            </div>
                            <div class="form-group">
                                <label>Position Type</label>
                                <select name="position_type">
                                    <option value="Employee" selected>Employee</option>
                                    <option value="Intern">Intern</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" style="text-transform: lowercase;">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" maxlength="11" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on leave">On Leave</option>
                                </select>
                            </div>
                            <div class="modal-actions">
                                <button type="submit" class="add-btn"><i class="bi bi-plus-lg"></i> Add Employee</button>
                                <button type="button" class="close-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Edit Employee Modal -->
                <div id="editEmployeeModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit Employee</h2>
                        <form method="POST" action="../backend/employee.php">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="employee_id" id="employeeId">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" id="editFirstName" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" id="editMiddleName">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" id="editLastName" required>
                            </div>
                            <div class="form-group">
                                <label>Hire Date</label>
                                <input type="text" id="editDatePicker" name="hire_date" placeholder="Select Date..">
                            </div>
                            <div class="form-group">
                                <label>Branch</label>
                                <select name="branch_id" id="editBranchId" required>
                                    <option value="">Select Branch</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?= $branch['id'] ?>">
                                            <?= htmlspecialchars($branch['branch_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Position</label>
                                <input type="text" name="position" id="editPosition" required>
                            </div>
                            <div class="form-group">
                                <label>Position Type</label>
                                <select name="position_type" id="editPositionType">
                                    <option value="Employee">Employee</option>
                                    <option value="Intern">Intern</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="editEmail" style="text-transform: lowercase;">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" id="editPhone">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on leave">On Leave</option>
                                </select>
                            </div>
                            <div class="modal-actions">
                                <button type="submit" class="add-btn"><i class="bi bi-check-lg"></i> Save Changes</button>
                                <button type="button" class="close-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <?php include '../components/notif.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/modal.js"></script>
    <script src="../assets/js/delete.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const editModal = document.getElementById("editEmployeeModal");
            const editCloseBtn = editModal.querySelector(".close");
            const editCancelBtn = editModal.querySelector(".close-btn");
            const editButtons = document.querySelectorAll(".edit-btn");

            flatpickr("#editDatePicker", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                disableMobile: "true",
                static: true,
                onReady: function(selectedDates, dateStr, instance) {
                    const calendar = instance.calendarContainer;
                    calendar.style.borderRadius = "12px";
                    calendar.style.boxShadow = "0 10px 25px rgba(0,0,0,0.1)";
                    calendar.style.zIndex = "9999";
                }
            });

            // Close modal function
            const closeEditModal = () => {
                editModal.classList.remove("show");
                setTimeout(() => {
                    editModal.style.display = "none";
                }, 300);
            };

            // Open modal with employee data
            editButtons.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();

                    const employeeId = this.dataset.id;
                    const firstName = this.dataset.firstName;
                    const middleName = this.dataset.middleName;
                    const lastName = this.dataset.lastName;
                    const branchId = this.dataset.branchId;
                    const position = this.dataset.position;
                    const positionType = this.dataset.positionType;
                    const email = this.dataset.email;
                    const phone = this.dataset.phone;
                    const hireDate = this.dataset.hireDate;
                    const status = this.dataset.status;

                    document.getElementById("employeeId").value = employeeId;
                    document.getElementById("editFirstName").value = firstName;
                    document.getElementById("editMiddleName").value = middleName;
                    document.getElementById("editLastName").value = lastName;
                    document.getElementById("editBranchId").value = branchId;
                    document.getElementById("editPosition").value = position;
                    document.getElementById("editPositionType").value = positionType;
                    document.getElementById("editEmail").value = email;
                    document.getElementById("editPhone").value = phone;
                    document.getElementById("editStatus").value = status;

                    // Set date picker value
                    flatpickr("#editDatePicker", {
                        altInput: true,
                        altFormat: "F j, Y",
                        dateFormat: "Y-m-d",
                        defaultDate: hireDate,
                        disableMobile: "true",
                        static: true,
                    });

                    // Show modal
                    editModal.style.display = "block";
                    setTimeout(() => {
                        editModal.classList.add("show");
                    }, 10);
                });
            });

            // Close modal events
            editCloseBtn.addEventListener("click", closeEditModal);
            editCancelBtn.addEventListener("click", closeEditModal);

            // Close when clicking outside modal
            window.addEventListener("click", (e) => {
                if (e.target === editModal) closeEditModal();
            });
        });
    </script>
</body>

</html>