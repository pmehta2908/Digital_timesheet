<?php
require_once '../config/db.php';
$pageTitle = "View Timesheets";
$activePage = "timesheets";
require_once '../includes/header.php';
requireAdmin();

$success = $error = '';

// Process form submission for editing/deleting timesheet
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'edit') {
        // Get form data and sanitize
        $id = $_POST['id'];
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
            // Update timesheet entry
            $sql = "UPDATE timesheets SET date = ?, task_name = ?, description = ?, start_time = ?, 
                    end_time = ?, total_hours = ?, assigned_by = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssi", $date, $task_name, $description, $start_time, 
                                  $end_time, $total_hours, $assigned_by, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Timesheet entry updated successfully";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    } else if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
        $id = $_POST['id'];
        
        // Delete timesheet entry
        $sql = "DELETE FROM timesheets WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Timesheet entry deleted successfully";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

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

// Get employees for filter
$sql_employees = "SELECT id, name FROM employees WHERE role = 'user' ORDER BY name";
$result_employees = mysqli_query($conn, $sql_employees);

// Get unique assigned_by values for filter
$sql_assigned = "SELECT DISTINCT assigned_by FROM timesheets ORDER BY assigned_by";
$result_assigned = mysqli_query($conn, $sql_assigned);
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5>Filter Timesheets</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
            <div class="col-md-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select class="form-select" id="employee_id" name="employee_id">
                    <option value="">All Employees</option>
                    <?php while ($row_employee = mysqli_fetch_assoc($result_employees)): ?>
                        <option value="<?php echo $row_employee['id']; ?>" <?php echo $employee_id == $row_employee['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row_employee['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-3">
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
        <h5>Timesheet Entries</h5>
        <div>
            <span class="badge bg-primary me-2"><?php echo mysqli_num_rows($result); ?> entries</span>
            <a href="export.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-sm btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Task</th>
                            <th>Description</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Hours</th>
                            <th>Assigned By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo date('h:i A', strtotime($row['start_time'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($row['end_time'])); ?></td>
                                <td><?php echo $row['total_hours']; ?></td>
                                <td><?php echo htmlspecialchars($row['assigned_by']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-btn" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-employee="<?php echo htmlspecialchars($row['employee_name']); ?>"
                                            data-date="<?php echo $row['date']; ?>"
                                            data-task="<?php echo htmlspecialchars($row['task_name']); ?>"
                                            data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                            data-start="<?php echo $row['start_time']; ?>"
                                            data-end="<?php echo $row['end_time']; ?>"
                                            data-hours="<?php echo $row['total_hours']; ?>"
                                            data-assigned="<?php echo htmlspecialchars($row['assigned_by']); ?>"
                                            data-bs-toggle="modal" data-bs-target="#editModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="<?php echo $row['id']; ?>"
                                            data-employee="<?php echo htmlspecialchars($row['employee_name']); ?>"
                                            data-date="<?php echo date('M d, Y', strtotime($row['date'])); ?>"
                                            data-task="<?php echo htmlspecialchars($row['task_name']); ?>"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No timesheet entries found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Timesheet Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee</label>
                            <input type="text" class="form-control" id="edit_employee" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="edit_date" name="date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_task_name" class="form-label">Project/Task Name</label>
                        <input type="text" class="form-control" id="edit_task_name" name="task_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_total_hours" class="form-label">Total Hours</label>
                            <input type="number" class="form-control" id="edit_total_hours" name="total_hours" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_assigned_by" class="form-label">Assigned By</label>
                        <select class="form-select" id="edit_assigned_by" name="assigned_by" required>
                            <option value="Santosh">Santosh</option>
                            <option value="Dharmendar">Dharmendar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this timesheet entry?</p>
                <p><strong>Employee:</strong> <span id="delete_employee"></span></p>
                <p><strong>Date:</strong> <span id="delete_date"></span></p>
                <p><strong>Task:</strong> <span id="delete_task"></span></p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Set modal data when edit button is clicked
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.getAttribute('data-id');
                document.getElementById('edit_employee').value = this.getAttribute('data-employee');
                document.getElementById('edit_date').value = this.getAttribute('data-date');
                document.getElementById('edit_task_name').value = this.getAttribute('data-task');
                document.getElementById('edit_description').value = this.getAttribute('data-description');
                document.getElementById('edit_start_time').value = this.getAttribute('data-start');
                document.getElementById('edit_end_time').value = this.getAttribute('data-end');
                document.getElementById('edit_total_hours').value = this.getAttribute('data-hours');
                document.getElementById('edit_assigned_by').value = this.getAttribute('data-assigned');
            });
        });
        
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_id').value = this.getAttribute('data-id');
                document.getElementById('delete_employee').textContent = this.getAttribute('data-employee');
                document.getElementById('delete_date').textContent = this.getAttribute('data-date');
                document.getElementById('delete_task').textContent = this.getAttribute('data-task');
            });
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>