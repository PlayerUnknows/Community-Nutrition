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

    <style>
        /*body {
            background: linear-gradient(135deg, #007bff, #ffffff) no-repeat center center fixed;
            min-height: 100vh;
            background-attachment: fixed; /* This prevents gradient from repeating on scroll */

        :root {
            --primary-blue: #007bff;
            --light-blue: #63a4ff;
            --logo-width: 50px;
            /* Default logo width */
            --logo-height: 60px;
            /* Slightly increased logo height */
            --profile-img-width: 40px;
            /* Default profile image width */
            --profile-img-height: 70px;
            /* Slightly increased profile image height */
        }

        .bg-primary {
            background-color: white !important;
        }

        /* Responsive Logo Styling */
        .logo-container {
            display: flex;
            align-items: center;
        }

        .responsive-logo {
            width: var(--logo-width);
            height: var(--logo-height);
            object-fit: cover;
            transition: all 0.3s ease;
        }

        /* Dropdown Toggle Styling */
        .profile-dropdown {
            position: relative;
            display: flex;
            align-items: center;
        }

        .profile-dropdown img.dropdown-toggle {
            width: var(--profile-img-width);
            height: var(--profile-img-height);
            border-radius: 40%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .profile-dropdown img.dropdown-toggle:hover {
            transform: scale(1.05);
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .responsive-logo {
                width: 70px;
                height: 70px;
            }

            .profile-dropdown img.dropdown-toggle {
                width: 65px;
                height: 65px;
            }
        }

        @media (min-width: 577px) and (max-width: 768px) {
            .responsive-logo {
                width: 90px;
                height: 90px;
            }

            .profile-dropdown img.dropdown-toggle {
                width: 75px;
                height: 75px;
            }
        }

        @media (min-width: 1200px) {
            .responsive-logo {
                width: 120px;
                height: 120px;
            }

            .profile-dropdown img.dropdown-toggle {
                width: 95px;
                height: 95px;
            }
        }

        /* Remove problematic margins */
        .responsive-logo,
        .dropdown-toggle {
            margin-left: 0;
            margin-right: 0;
        }

        /* Flex container adjustments for header */
        .container.d-flex.align-items-center.justify-content-between {
            gap: 10px;
            padding: 0 15px;
        }

        /* Center-align text for the welcome and date/time display */
        .text-center {
            font-size: 14px;
            text-align: center;
        }

        /* Optional: Add some padding or spacing for better alignment */
        .text-center p {
            margin: 0;
        }

        .error {
            border-color: #dc3545 !important;
        }

        .error-msg {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
            display: block;
        }

        /* Sub-navigation styling */
        #monitoring-container,
        #acc-reg-container {
            position: relative;
        }

        .sub-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0 0 4px 4px;
            z-index: 1000;
            min-width: 150px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #monitoring-container:hover .sub-nav,
        #acc-reg-container:hover .sub-nav {
            display: block;
        }

        .sub-nav-button {
            display: block;
            width: 100%;
            padding: 8px 16px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .sub-nav-button:hover {
            background-color: #f8f9fa;
        }

        .sub-content {
            display: none;
        }

        /* Small SweetAlert2 Modal Styles */
        .small-modal {
            font-size: 0.9rem !important;
        }

        .small-modal-title {
            font-size: 1.1rem !important;
            padding: 0.5rem 0 !important;
        }

        .small-modal-content {
            font-size: 0.9rem !important;
            margin-top: 0.5rem !important;
        }

        .small-modal .swal2-icon {
            width: 3em !important;
            height: 3em !important;
            margin: 0.5em auto !important;
        }

        .small-modal .swal2-icon .swal2-icon-content {
            font-size: 1.75em !important;
        }

        .small-modal .swal2-actions {
            margin: 0.5em auto 0 !important;
        }

        .small-modal .swal2-styled {
            padding: 0.25em 0.75em !important;
            font-size: 0.9em !important;
        }

        /* Update loading screen styles */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 1.9s ease;

        }

        .loading-logo {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            animation: pulse 3s ease-in-out infinite;

        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1.05s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }

        }

        .header-clock {
            display: flex;
            gap: 1rem;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .header-clock span {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Profile and Display Settings Modal Styles */
        .profile-settings-modal .swal2-popup,
        .display-settings-modal .swal2-popup {
            padding: 2rem;
        }

        .profile-settings-modal .form-label,
        .display-settings-modal .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .profile-settings-modal .form-control,
        .display-settings-modal .form-control,
        .display-settings-modal .form-select {
            margin-bottom: 1rem;
        }

        .profile-settings-modal .btn-group {
            width: 100%;
        }

        .profile-settings-modal .btn-group .btn {
            flex: 1;
        }
    </style>

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
                    <li><a class="dropdown-item" href="#" id="displaySettingsBtn">
                            <i class="fas fa-cog"></i> Display Settings
                        </a></li>
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
                <div id="patientProfileContainer" class="container mt-4">
                    <!-- Patient profile content will be loaded here -->
                    <?php include 'patient_profile.php'; ?>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                <div class="container mt-4">
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
                <div id="eventFormContainer" class="container mt-4">
                    <!-- Event form will be loaded here -->
                    <?php include 'event.php'; ?>
                </div>
            </div>

            <!-- Audit Trail Section -->
            <div class="tab-pane fade" id="audit" role="tabpanel" aria-labelledby="audit-tab">
                <div class="card">
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
                                        <th>Count</th>
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

    <!-- Custom Scripts - Load in specific order -->
    <script src="../script/monitoring.js"></script>
    <script src="../script/bmi_statistics.js"></script>
    <script src="../script/audit_trail.js"></script>
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