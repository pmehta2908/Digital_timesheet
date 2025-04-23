<?php
require_once '../config/db.php';
$pageTitle = "View Timesheets";
$activePage = "view";
require_once '../includes/header.php';

$employee_id = $_SESSION['user_id'];

// Filter parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$assigned_by = isset($_GET['assigned_by']) ? $_GET['assigned_by'] : '';

// Build query
$sql = "SELECT * FROM timesheets WHERE employee_id = ?";
$params = [$employee_id];
$types = "i";

if (!empty($month)) {
    $sql .= " AND DATE_FORMAT(date, '%Y-%m') = ?";
    $params[] = $month;
    $types .= "s";
}

if (!empty($assigned_by)) {
    $sql .= " AND assigned_by = ?";
    $params[] = $assigned_by;
    $types .= "s";
}

$sql .= " ORDER BY date DESC, start_time DESC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get unique assigned_by values for filter
$sql_assigned = "SELECT DISTINCT assigned_by FROM timesheets WHERE employee_id = ? ORDER BY assigned_by";
$stmt_assigned = mysqli_prepare($conn, $sql_assigned);
mysqli_stmt_bind_param($stmt_assigned, "i", $employee_id);
mysqli_stmt_execute($stmt_assigned);
$result_assigned = mysqli_stmt_get_result($stmt_assigned);
?>

<div class="card mb-4">
    <div class="card-header">
        <h5>Filter Timesheets</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
            <div class="col-md-5">
                <label for="month" class="form-label">Month</label>
                <input type="month" class="form-control" id="month" name="month" value="<?php echo $month; ?>">
            </div>
            <div class="col-md-5">
                <label for="assigned_by" class="form-label">Assigned By</label>
                <select class="form-select" id="assigned_by" name="assigned_by">
                    <option value="">All</option>
                    <?php while ($row_assigned = mysqli_fetch_assoc($result_assigned)): ?>
                        <option value="<?php echo htmlspecialchars($row_assigned['assigned_by']); ?>" <?php echo $assigned_by == $row_assigned['assigned_by'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row_assigned['assigned_by']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Your Timesheet Entries</h5>
        <span class="badge bg-primary"><?php echo mysqli_num_rows($result); ?> entries</span>
    </div>
    <div class="card-body">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Task</th>
                        <th>Description</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Hours</th>
                        <th>Assigned By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['start_time'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['end_time'])); ?></td>
                            <td><?php echo $row['total_hours']; ?></td>
                            <td><?php echo htmlspecialchars($row['assigned_by']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No timesheet entries found. <a href="submit_timesheet.php">Submit your first timesheet</a>.</p>
    <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>