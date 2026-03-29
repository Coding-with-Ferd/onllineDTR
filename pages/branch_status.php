<?php
require_once '../config/session.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}
include '../backend/branch.php';
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
    <link rel="stylesheet" href="../assets/branch.css">
</head>

<body>
    <div class="dashboard">
        <?php include '../components/sidebar.php'; ?>
        <div class="main">
            <?php include '../components/header.php'; ?>

            <div class="main-layer">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
                    <div>
                        <h1 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin:0;">
                            <i class="bi bi-geo-alt-fill" style="color: #1a7318;"></i> Branch Status
                        </h1>
                        <p style="color: #64748b; margin-top: 5px;">Live monitoring of clinic operations and availability.</p>
                    </div>

                    <button type="button" class="btn-add-branch" onclick="openAddBranchModal()">
                        <i class="bi bi-plus-circle"></i> Add Branch
                    </button>
                </div>

                <div class="branch-grid">
                    <?php if (!empty($branches)): ?>
                        <?php foreach ($branches as $branch): ?>
                            <?php
                            $statusClass = !empty($branch['is_open']) ? 'status-open' : 'status-closed';
                            $statusText  = !empty($branch['is_open']) ? 'Open' : 'Closed';
                            ?>
                            <div class="branch-card">
                                <div class="branch-header">
                                    <div class="branch-info">
                                        <h3><?= htmlspecialchars($branch['branch_name']) ?></h3>
                                        <p>
                                            <i class="bi bi-clock"></i>
                                            <?= date('g:i A', strtotime($branch['open_time'])) ?>
                                            -
                                            <?= date('g:i A', strtotime($branch['close_time'])) ?>
                                        </p>
                                    </div>

                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <button type="button"
                                            class="btn-edit-branch"
                                            onclick="openEditBranchModal(
                                '<?= $branch['id'] ?>',
                                '<?= htmlspecialchars(addslashes($branch['branch_name'])) ?>',
                                '<?= htmlspecialchars(addslashes($branch['address'])) ?>',
                                '<?= substr($branch['open_time'], 0, 5) ?>',
                                '<?= substr($branch['close_time'], 0, 5) ?>',
                                '<?= $branch['is_open'] ?>'
                            )">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <span class="status-indicator <?= $statusClass ?>"><?= $statusText ?></span>
                                    </div>
                                </div>

                                <div class="branch-metrics">
                                    <div class="metric-item">
                                        <span class="metric-value"><?= str_pad((string)$branch['staff_on_duty'], 2, '0', STR_PAD_LEFT) ?></span>
                                        <span class="metric-label">Staff On Duty</span>
                                    </div>

                                    <div class="metric-item" style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                                        <span class="metric-value"><?= str_pad((string)$branch['absent_today'], 2, '0', STR_PAD_LEFT) ?></span>
                                        <span class="metric-label">Absent Today</span>
                                    </div>

                                    <div class="metric-item">
                                        <span class="metric-value"><?= str_pad((string)$branch['on_leave'], 2, '0', STR_PAD_LEFT) ?></span>
                                        <span class="metric-label">On Leave</span>
                                    </div>
                                </div>

                                <div class="branch-footer">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span><?= htmlspecialchars($branch['address'] ?? 'No address available') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No branches found.</p>
                    <?php endif; ?>
                </div>

                <div id="addBranchModal" class="modal-overlay">
                    <div class="modal-box">
                        <div class="modal-top">
                            <h2><i class="bi bi-building-add"></i> Add Branch</h2>
                            <button type="button" class="modal-close" onclick="closeAddBranchModal()">&times;</button>
                        </div>

                        <form action="../backend/branch.php" method="POST">
                            <div class="modal-form-group">
                                <label>Branch Name</label>
                                <input type="text" name="branch_name" required>
                            </div>

                            <div class="modal-form-group">
                                <label>Address</label>
                                <textarea name="address" rows="3" placeholder="Enter branch address"></textarea>
                            </div>

                            <div class="modal-time-grid">
                                <div class="modal-form-group">
                                    <label>Open Time</label>
                                    <input type="time" name="open_time" value="09:00" required>
                                </div>

                                <div class="modal-form-group">
                                    <label>Close Time</label>
                                    <input type="time" name="close_time" value="18:00" required>
                                </div>
                            </div>

                            <div class="modal-form-group">
                                <label>Status</label>
                                <select name="is_open">
                                    <option value="1">Open</option>
                                    <option value="0">Closed</option>
                                </select>
                            </div>

                            <div class="modal-actions">
                                <button type="submit" class="btn-save-branch">Save Branch</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="editBranchModal" class="modal-overlay">
                    <div class="modal-box">
                        <div class="modal-top">
                            <h2><i class="bi bi-pencil-square"></i> Edit Branch</h2>
                            <button type="button" class="modal-close" onclick="closeEditBranchModal()">&times;</button>
                        </div>

                        <form action="../backend/branch.php" method="POST">
                            <input type="hidden" name="branch_id" id="edit_branch_id">
                            <input type="hidden" name="action" value="update">

                            <div class="modal-form-group">
                                <label>Branch Name</label>
                                <input type="text" name="branch_name" id="edit_branch_name" required>
                            </div>

                            <div class="modal-form-group">
                                <label>Address</label>
                                <textarea name="address" id="edit_branch_address" rows="3"></textarea>
                            </div>

                            <div class="modal-time-grid">
                                <div class="modal-form-group">
                                    <label>Open Time</label>
                                    <input type="time" name="open_time" id="edit_open_time" required>
                                </div>

                                <div class="modal-form-group">
                                    <label>Close Time</label>
                                    <input type="time" name="close_time" id="edit_close_time" required>
                                </div>
                            </div>

                            <div class="modal-form-group">
                                <label>Status</label>
                                <select name="is_open" id="edit_is_open">
                                    <option value="1">Open</option>
                                    <option value="0">Closed</option>
                                </select>
                            </div>

                            <div class="modal-actions">
                                <button type="submit" class="btn-save-branch">Update Branch</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/branch.js"></script>
</body>

</html>