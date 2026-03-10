<?php
session_start();
include '../auth/db_connect.php';

// Fetch all employees
$query = $conn->query("SELECT * FROM employees ORDER BY last_name ASC");
$employees = $query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - PrimeHealth</title>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/addemployee.css">

    <style>
        /* Table styling */
        .employee-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family:'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
        }

        .employee-table th,
        .employee-table td {
            padding: 12px 15px;
            border: 1px solid #1a8e4a;
            text-align: left;
        }

        .employee-table th {
            background: linear-gradient(135deg, #0b7a3b 0%, #1a8e4a 50%, #0b7a3b 100%);
            color: #fff;
        }

        .employee-table tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .add-btn {
            background: linear-gradient(135deg, #0b7a3b 0%, #1a8e4a 50%, #0b7a3b 100%);
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .add-btn:hover {
            opacity: 0.9;
        }

        /* Edit and Delete buttons in table */
        .table-action a {
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-right: 5px;
        }

        .table-action a.edit {
            background-color: #d0e7ff;
            color: #0b7a3b;
        }

        .table-action a.edit:hover {
            background-color: #a8d0ff;
        }

        .table-action a.delete {
            background-color: #ffd0d0;
            color: #b30000;
        }

        .table-action a.delete:hover {
            background-color: #ffaaaa;
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

            <!-- Main content -->
            <div class="main-layer">
                <div class="content">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h1>Employees</h1>
                        <a href="add_employee.php" class="add-btn"><i class="bi bi-plus-lg"></i> Add Employee</a>
                    </div>

                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th>Employee Number</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Position Type</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($employees) > 0): ?>
                                <?php foreach ($employees as $emp): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($emp['employee_code']) ?></td>
                                        <td>
                                            <a href="profile.php?id=<?= $emp['id'] ?>">
                                                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($emp['position']) ?></td>
                                        <td><?= ucfirst($emp['position_type']) ?></td>
                                        <td><?= htmlspecialchars($emp['email']) ?></td>
                                        <td><?= htmlspecialchars($emp['phone']) ?></td>
                                        <td><?= ucfirst($emp['status']) ?></td>
                                        <td class="table-action">
                                            <a href="edit_employee.php?id=<?= $emp['id'] ?>" class="edit">Edit</a>
                                            <a href="../backend/employee.php?id=<?= $emp['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">No employees found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>

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
                                <input type="email" name="email">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone">
                            </div>
                            <div class="form-group">
                                <label>Hire Date</label>
                                <input type="date" name="hire_date" required>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="modal-actions">
                                <button type="submit" class="add-btn"><i class="bi bi-plus-lg"></i> Add Employee</button>
                                <button type="button" class="close-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/modal.js"></script>
</body>

</html>