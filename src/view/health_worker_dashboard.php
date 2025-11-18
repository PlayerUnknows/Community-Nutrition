<?php
require_once __DIR__ . '/../backend/check_role_access.php';

// Only allow role 2 (Health Worker) to access this page
checkUserRole([2]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Worker Dashboard - Community Nutrition Information System</title>
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.min.css">
    <style>
        /* Include dashboard styles directly */
        body {
            background-color: #f8f9fa;
        }
        .container {
            padding: 2rem;
        }
        .dashboard-header {
            margin-bottom: 2rem;
        }
        .logout-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        .card {
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Health Worker Dashboard</h1>
            <button id="logoutButton" class="btn btn-danger logout-button">Logout</button>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Patient Records</h5>
                        <p class="card-text">Manage and view patient health records.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Community Health</h5>
                        <p class="card-text">Monitor community health statistics and trends.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery library -->
    <script src="../../node_modules/jquery/dist/jquery.js"></script>
    <!-- Include Popper.js for Bootstrap tooltips -->
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.js"></script>
    <!-- Include Bootstrap JavaScript -->
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Include SweetAlert2 JavaScript -->
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <!-- Link to your separate JS file -->
    <script src="../../src/script/logout.js"></script>
</body>
</html>
