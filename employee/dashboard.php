<?php
require_once '../config/db.php';
$pageTitle = "Employee Dashboard";
$activePage = "dashboard";
require_once '../includes/header.php';

// Get employee's recent timesheets
$employee_id = $_SESSION['user_id'];
$sql = "SELECT * FROM timesheets WHERE employee_id = ? ORDER BY date DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total hours for current month
$current_month = date('Y-m');
$sql_hours = "SELECT SUM(total_hours) as total FROM timesheets WHERE employee_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
$stmt_hours = mysqli_prepare($conn, $sql_hours);
mysqli_stmt_bind_param($stmt_hours, "is", $employee_id, $current_month);
mysqli_stmt_execute($stmt_hours);
$result_hours = mysqli_stmt_get_result($stmt_hours);
$total_hours = mysqli_fetch_assoc($result_hours)['total'] ?? 0;
?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Monthly Hours</h5>
                <h2 class="display-4"><?php echo number_format($total_hours, 2); ?></h2>
                <p class="text-muted">Total hours for <?php echo date('F Y'); ?></p>
                <a href="submit_timesheet.php" class="btn btn-primary">Submit New Timesheet</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="list-group">
                    <a href="submit_timesheet.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock me-2"></i> Submit Timesheet
                    </a>
                    <a href="view_timesheets.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-table me-2"></i> View All Timesheets
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Recent Timesheet Entries</h5>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Task</th>
                            <th>Hours</th>
                            <th>Assigned By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                                <td><?php echo $row['total_hours']; ?></td>
                                <td><?php echo htmlspecialchars($row['assigned_by']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <a href="view_timesheets.php" class="btn btn-outline-primary">View All Entries</a>
        <?php else: ?>
            <p class="text-muted">No timesheet entries found. <a href="submit_timesheet.php">Submit your first timesheet</a>.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
