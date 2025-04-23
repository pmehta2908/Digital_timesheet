<?php
require_once '../auth/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Timesheet System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: #fff;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .content {
            padding: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4>Timesheet System</h4>
                        <p>Welcome, <?php echo $_SESSION['name']; ?></p>
                    </div>
                    <ul class="nav flex-column">
                        <?php if (isAdmin()): ?>
                            <!-- Admin Menu -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo $activePage == 'dashboard' ? 'active' : ''; ?>" href="../admin/dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $activePage == 'employees' ? 'active' : ''; ?>" href="../admin/manage_employees.php">
                                    <i class="bi bi-people me-2"></i> Manage Employees
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $activePage == 'timesheets' ? 'active' : ''; ?>" href="../admin/view_timesheets.php">
                                    <i class="bi bi-table me-2"></i> View Timesheets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $activePage == 'export' ? 'active' : ''; ?>" href="../admin/export.php">
                                    <i class="bi bi-download me-2"></i> Export Reports
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- Employee Menu -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo $activePage == 'dashboard' ? 'active' : ''; ?>" href="../employee/dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $activePage == 'submit' ? 'active' : ''; ?>" href="../employee/submit_timesheet.php">
                                    <i class="bi bi-clock me-2"></i> Submit Timesheet
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $activePage == 'view' ? 'active' : ''; ?>" href="../employee/view_timesheets.php">
                                    <i class="bi bi-table me-2"></i> View Timesheets
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $pageTitle ?? 'Timesheet System'; ?></h1>
                </div>
