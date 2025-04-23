<?php
require_once '../config/db.php';
$pageTitle = "Submit Timesheet";
$activePage = "submit";
require_once '../includes/header.php';

$success = $error = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $employee_id = $_SESSION['user_id'];
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $total_hours = mysqli_real_escape_string($conn, $_POST['total_hours']);
    $assigned_by = mysqli_real_escape_string($conn, $_POST['assigned_by']);
    
    // Validate input
    if (empty($date) || empty($task_name) || empty($description) || empty($start_time) || 
        empty($end_time) || empty($total_hours) || empty($assigned_by)) {
        $error = "All fields are required";
    } else {
        // Insert timesheet entry
        $sql = "INSERT INTO timesheets (employee_id, date, task_name, description, start_time, end_time, total_hours, assigned_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssssds", $employee_id, $date, $task_name, $description, $start_time, $end_time, $total_hours, $assigned_by);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Timesheet entry submitted successfully";
            // Reset form
            $date = $task_name = $description = $start_time = $end_time = $total_hours = $assigned_by = '';
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Submit New Timesheet Entry</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="task_name" class="form-label">Project/Task Name</label>
                    <input type="text" class="form-control" id="task_name" name="task_name" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description of Work Done</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="total_hours" class="form-label">Total Hours</label>
                    <input type="number" class="form-control" id="total_hours" name="total_hours" step="0.01" readonly required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="assigned_by" class="form-label">Assigned By</label>
                <select class="form-select" id="assigned_by" name="assigned_by" required>
                    <option value="">Select HOD</option>
                    <option value="Santosh">Santosh</option>
                    <option value="Dharmendar">Dharmendar</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Submit Timesheet</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>