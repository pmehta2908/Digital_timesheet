<?php
require_once '../config/db.php';
require_once '../auth/session.php';
requireLogin();
requireAdmin();

// Check if export is requested
if (isset($_GET['format']) && $_GET['format'] == 'csv') {
    // Filter parameters
    $employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $assigned_by = isset($_GET['assigned_by']) ? $_GET['assigned_by'] : '';

    // Build query
    $sql = "SELECT t.*, e.name as employee_name FROM timesheets t JOIN employees e ON t.employee_id = e.id WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($employee_id)) {
        $sql .= " AND t.employee_id = ?";
        $params[] = $employee_id;
        $types .= "i";
    }

    if (!empty($start_date)) {
        $sql .= " AND t.date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }

    if (!empty($end_date)) {
        $sql .= " AND t.date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }

    if (!empty($assigned_by)) {
        $sql .= " AND t.assigned_by = ?";
        $params[] = $assigned_by;
        $types .= "s";
    }

    $sql .= " ORDER BY t.date DESC, t.start_time DESC";

    // Prepare and execute query
    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="timesheet_export_' . date('Y-m-d') . '.csv"');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, ['Employee', 'Date', 'Task', 'Description', 'Start Time', 'End Time', 'Total Hours', 'Assigned By']);

    // Fetch the data and output as CSV
    while ($row = mysqli_fetch_assoc($result)) {
        $csv_row = [
            $row['employee_name'],
            $row['date'],
            $row['task_name'],
            $row['description'],
            $row['start_time'],
            $row['end_time'],
            $row['total_hours'],
            $row['assigned_by']
        ];
        fputcsv($output, $csv_row);
    }

    // Close the file pointer
    fclose($output);
    exit;
}

$pageTitle = "Export Reports";
$activePage = "export";
require_once '../includes/header.php';

// Get employees for filter
$sql_employees = "SELECT id, name FROM employees WHERE role = 'user' ORDER BY name";
$result_employees = mysqli_query($conn, $sql_employees);

// Get unique assigned_by values for filter
$sql_assigned = "SELECT DISTINCT assigned_by FROM timesheets ORDER BY assigned_by";
$result_assigned = mysqli_query($conn, $sql_assigned);
?>

<div class="card">
    <div class="card-header">
        <h5>Export Timesheet Reports</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="export.php" class="row g-3">
            <input type="hidden" name="format" value="csv">
            
            <div class="col-md-6 mb-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select class="form-select" id="employee_id" name="employee_id">
                    <option value="">All Employees</option>
                    <?php while ($row_employee = mysqli_fetch_assoc($result_employees)): ?>
                        <option value="<?php echo $row_employee['id']; ?>">
                            <?php echo htmlspecialchars($row_employee['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="assigned_by" class="form-label">Assigned By</label>
                <select class="form-select" id="assigned_by" name="assigned_by">
                    <option value="">All</option>
                    <?php while ($row_assigned = mysqli_fetch_assoc($result_assigned)): ?>
                        <option value="<?php echo htmlspecialchars($row_assigned['assigned_by']); ?>">
                            <?php echo htmlspecialchars($row_assigned['assigned_by']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-download"></i> Export to CSV
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5>Export Instructions</h5>
    </div>
    <div class="card-body">
        <p>Use the form above to filter and export timesheet data:</p>
        <ul>
            <li>Leave all fields blank to export all timesheet entries</li>
            <li>Select an employee to export entries for a specific employee</li>
            <li>Set date range to export entries within a specific period</li>
            <li>Select "Assigned By" to filter by HOD</li>
        </ul>
        <p>The exported CSV file can be opened in Excel or any spreadsheet application for further analysis.</p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>