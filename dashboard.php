<?php
require_once '../config/db.php';
$pageTitle = "Admin Dashboard";
$activePage = "dashboard";
require_once '../includes/header.php';
requireAdmin();

// Get total employees
$sql_employees = "SELECT COUNT(*) as total FROM employees WHERE role = 'user'";
$result_employees = mysqli_query($conn, $sql_employees);
$total_employees = mysqli_fetch_assoc($result_employees)['total'];

// Get total timesheets
$sql_timesheets = "SELECT COUNT(*) as total FROM timesheets";
$result_timesheets = mysqli_query($conn, $sql_timesheets);
$total_timesheets = mysqli_fetch_assoc($result_timesheets)['total'];

// Get total hours for current month
$current_month = date('Y-m');
$sql_hours = "SELECT SUM(total_hours) as total FROM timesheets WHERE DATE_FORMAT(date, '%Y-%m') = ?";
$stmt_hours = mysqli_prepare($conn, $sql_hours);
mysqli_stmt_bind_param($stmt_hours, "s", $current_month);
mysqli_stmt_execute($stmt_hours);
$result_hours = mysqli_stmt_get_result($stmt_hours);
$total_hours = mysqli_fetch_assoc($result_hours)['total'] ?? 0;

// Get recent timesheets
$sql_recent = "SELECT t.*, e.name as employee_name FROM timesheets t 
               JOIN employees e ON t.employee_id = e.id 
               ORDER BY t.date DESC, t.created_at DESC LIMIT 5";
$result_recent = mysqli_query($conn, $sql_recent);
?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Employees</h6>
                        <h2 class="display-4"><?php echo $total_employees; ?></h2>
                    </div>
                    <i class="bi bi-people display-4"></i>
                </div>
                <a href="manage_employees.php" class="text-white">Manage Employees <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Timesheets</h6>
                        <h2 class="display-4"><?php echo $total_timesheets; ?></h2>
                    </div>
                    <i class="bi bi-table display-4"></i>
                </div>
                <a href="view_timesheets.php" class="text-white">View Timesheets <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Monthly Hours</h6>
                        <h2 class="display-4"><?php echo number_format($total_hours, 2); ?></h2>
                    </div>
                    <i class="bi bi-clock display-4"></i>
                </div>
                <span class="text-white">For <?php echo date('F Y'); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Recent Timesheet Entries</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result_recent) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Task</th>
                                    <th>Hours</th>
                                    <th>Assigned By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_recent)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
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
                    <p class="text-muted">No timesheet entries found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>