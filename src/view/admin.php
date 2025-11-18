<?php
require_once __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboad sub</title>
    <link rel="icon" href="../../assets/img/healthy-food.png">

    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css">
    <link rel="stylesheet" type="text/css" href="../../node_modules/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="../../assets/css/admin.css"/>

    <!-- Add this before </head> -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString();
            document.getElementById('current-time').textContent = timeString;
            document.getElementById('current-date').textContent = dateString;
        }

        // Update immediately and then every second
        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
</head>

<body>
    <!-- Update the loading screen div -->
    <div id="loading-screen">
        <img src="../../assets/img/SanAndres.svg" alt="Logo" class="loading-logo">
        <div class="loading-spinner"></div>
    </div>

    <!-- Header Section -->
    <div class="bg-white py-1 shadow-sm h-1 header-section">
        <div class="container d-flex align-items-center justify-content-between">
            <!-- Logo -->
            <div class="d-flex align-items-center logo-container">
                <img src="../../assets/img/SanAndres.svg" alt="San Andres Logo" class="responsive-logo" style="width: 65px; height: 65px;">
            </div>


            <div class="text-center">
                <p class="mb-0">Welcome, <strong id="username">
                        <?php
                        // Multiple checks to retrieve email
                        $displayEmail = "Guest";

                        if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
                            $email = htmlspecialchars($_SESSION['email']);
                        } elseif (isset($_SESSION['user']['email']) && !empty($_SESSION['user']['email'])) {
                            $email = htmlspecialchars($_SESSION['user']['email']);
                        }

                        if (isset($email)) {
                            // Remove '@gmail.com' if it exists
                            $displayEmail = str_replace('@gmail.com', '', $email);
                        }

                        echo $displayEmail;
                        ?>

                    </strong></p>

                <!-- Add this in your header where you want the clock -->
                <div class="header-clock">
                    <span id="current-date"></span>
                    <span id="current-time"></span>
                </div>


            </div>
            <!-- Profile Dropdown -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user"></i> Profile
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" id="profileSettingsBtn">
                            <i class="fas fa-user-cog"></i> Profile Settings
                        </a></li>
                    <!-- <li><a class="dropdown-item" href="#" id="displaySettingsBtn">
                            <i class="fas fa-cog"></i> Display Settings
                        </a></li> -->
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item logout-button" href="#" id="logoutButton">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                </ul>
            </div>
        </div>
    </div>


    <!-- Page Content -->
    <div class="container-fluid mt-4">
        <!-- Bootstrap Tab Navigation -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="patients-tab" data-bs-toggle="tab" data-bs-target="#patients" type="button" role="tab" aria-controls="patients" aria-selected="true" tabindex="0">Patients Profile</button>
            </li>
            <li class="nav-item" role="presentation" id="monitoring-container">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab" aria-controls="schedule" aria-selected="false" tabindex="-1">Nutrition Monitoring</button>
                <div class="sub-nav">
                    <button class="sub-nav-button" data-target="monitoring-records">Monitoring Records</button>
                    <button class="sub-nav-button" data-target="nutrition-report">Growth Trends</button>
                    <button class="sub-nav-button" data-target="arm-circumference">Arm Circumference</button>
                    <button class="sub-nav-button" data-target="bmi-statistics">BMI Statistics</button>
                    <button class="sub-nav-button" data-target="overall-report">OverAllReport</button>
                </div>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab" aria-controls="appointments" aria-selected="false" tabindex="-1">Appointments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="event-tab" data-bs-toggle="tab" data-bs-target="#event" type="button" role="tab" aria-controls="event" aria-selected="false" tabindex="-1">Event information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab" aria-controls="audit" aria-selected="false" tabindex="-1">Audit Trail</button>
            </li>
            <li class="nav-item" role="presentation" id="acc-reg-container">
                <button class="nav-link" id="acc-reg" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="false" tabindex="-1">Create Account</button>
                <!-- Sub-navigation -->
                <div class="sub-nav">
                    <button class="sub-nav-button" data-target="view-users">View Users</button>
                </div>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <!-- Patient Profile Section -->
            <div class="tab-pane fade show active" id="patients" role="tabpanel" aria-labelledby="patients-tab" tabindex="0">
                <div id="patientProfileContainer" class="w-60 mt-4">
                    <!-- Patient profile content will be loaded here -->
                    <?php include 'patient_profile.php'; ?>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                <div class="w-100 mt-4" style="padding: 0; margin: 0;">
                    <!-- Monitoring Records Content -->
                    <div id="monitoring-records" class="sub-content">
                        <?php include 'monitoring.php'; ?>
                    </div>
                    <!-- Nutrition Report Content -->
                    <div id="nutrition-report" class="sub-content" style="display: none;">
                        <?php include 'report.php'; ?>
                    </div>
                    <!-- Arm Circumference Content -->
                    <div id="arm-circumference" class="sub-content" style="display: none;">
                        <?php include 'arm_circumference_report.php'; ?>
                    </div>
                    <!-- BMI Statistics Content -->
                    <div id="bmi-statistics" class="sub-content" style="display: none;">
                        <?php include 'bmi_statistics.php'; ?>
                    </div>
                    <!-- Overall Report Content -->
                    <div id="overall-report" class="sub-content" style="display: none;">
                        <?php include 'overall_report.php'; ?>
                    </div>
                </div>
            </div>

            <!-- Appointments Section -->
            <div class="tab-pane fade" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                <?php include __DIR__ . '/appointments.php'; ?>
            </div>

            <!-- Event information Section -->
            <div class="tab-pane fade" id="event" role="tabpanel" aria-labelledby="event-tab">
                <div id="eventFormContainer" class="w-100 mt-4">
                    <!-- Event form will be loaded here -->
                    <?php include 'event.php'; ?>
                </div>
            </div>

            <!-- Audit Trail Section -->
            <div class="tab-pane fade" id="audit" role="tabpanel" aria-labelledby="audit-tab">
                <div class="w-100" style="padding: 0; margin: 0;">
                <div class="card"></div>
                    <div class="card-body">
                        <h5 class="card-title mb-4">Audit Trail</h5>

                        <!-- Search and length controls -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="search" class="form-control" id="auditSearch" placeholder="Search audit trail...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="auditsPerPage">
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>


                        <!-- Audit table -->
                        <div class="table-responsive">
                            <table id="auditTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Registration Section -->
            <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="acc-reg">
                <div class="container mt-4">
                    <!-- Account Registration Content -->
                    <div id="signupFormContainer" class="sub-content">
                        <?php include __DIR__ . '/signup.php'; ?>
                    </div>

                    <!-- View Users Content -->
                    <div id="view-users" class="sub-content" style="display: none;">
                        <?php include __DIR__ . '/users.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Scripts -->
    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/moment/moment.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/daterangepicker/daterangepicker.js"></script>
    <script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="../../node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="../../node_modules/lottie-web/build/player/lottie.min.js"></script>

    <!-- Custom Scripts - Load in specific order -->
    <script src="../script/monitoring.js"></script>
    <script src="../script/bmi_statistics.js"></script>
    <script src="../script/audit_trail_simple.js"></script>
    <script src="../script/appointments.js"></script>
    <script src="../script/users.js"></script>
    <script src="../script/overall_report.js"></script>
    <script src="../script/dropdrown.js"></script>
    <script src="../script/logout.js"></script>
    <script src="../script/session.js"></script>
    <script src="../script/admin.js"></script>

    <!-- Loading screen script -->
    <script>
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                loadingScreen.style.opacity = '0';
                loadingScreen.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    loadingScreen.style.display = 'none';
                }, 500);
            }
        });
    </script>
</body>

</html>