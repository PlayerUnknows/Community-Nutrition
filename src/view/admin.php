<?php
require '../../vendor/autoload.php';

use Katzgrau\KLogger\Logger;
use Psr\Log\LogLevel;

$logger = new Logger(__DIR__ . '/logs', LogLevel::DEBUG);
$logger->error('This is an error message');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/sweetalert2.css">
    <link rel="stylesheet" href="../../assets/css/datatable.css">


    <!-- Core JS - Order is important -->
    <script src="../../assets/dist/jquery.js"></script>
    <script src="../../assets/dist/datatable.js"></script>
    <script src="../../assets/dist/popper.js"></script>
    <script src="../../assets/dist/bootstrap.min.js"></script>
    <script src="../../assets/dist/sweetalert.js"></script>
    <script src="../../assets/dist/moment.js"></script>
    <script src="../../assets/dist/chart.js"></script>






    <style>
        :root {
            --primary-blue: #007bff;
            --light-blue: #63a4ff;
        }

        .bg-primary {
            background-color: var(--primary-blue) !important;
        }

        /* Responsive Logo Styling */
        .logo-container {
            display: flex;
            align-items: center;
        }

        .responsive-logo {
            width: 100px;
            height: 100px;
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
            width: 85px;
            height: 85px;
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

        /* Spinner for Loading */
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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

        #signupFormContainer .signup-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-top: 6px solid var(--primary-blue);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            margin: 0 auto;
            animation: glowAnimation 2s ease-in-out infinite alternate;
            transition: all 0.3s ease;
        }

        @keyframes glowAnimation {
            0% {
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            50% {
                box-shadow: 0 0 20px rgba(0, 123, 255, 0.4),
                    0 0 30px rgba(0, 123, 255, 0.2);
            }

            100% {
                box-shadow: 0 0 25px rgba(99, 164, 255, 0.5),
                    0 0 35px rgba(99, 164, 255, 0.3);
            }
        }

        #signupFormContainer .signup-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.2);
        }

        /* Sub-navigation styles */
        .sub-nav {
            display: none;
            position: absolute;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 200px;
        }

        .nav-item:hover .sub-nav {
            display: block;
        }

        .sub-nav-button {
            display: block;
            width: 100%;
            padding: 8px 15px;
            margin: 0;
            border: none;
            background: white;
            color: var(--primary-blue);
            text-align: left;
            transition: all 0.2s ease;
        }

        .sub-nav-button:hover {
            background: #f0f0f0;
            color: #333;
        }

        .sub-nav-button.active {
            background: var(--primary-blue);
            color: white;
        }

        /* Position the account tab item relatively for absolute positioning of sub-nav */
        #acc-reg-container {
            position: relative;
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <div class="bg-white py-2 shadow-sm h-1">
        <div class="container d-flex align-items-center justify-content-between">
            <!-- Logo -->
            <div class="d-flex align-items-center logo-container">
                <img src="../../assets/img/md.png" alt="San Andres Logo" class="responsive-logo">
            </div>

            <!-- Welcome Message and Date/Time -->
            <div class="text-center">
                <p class="mb-0">Welcome, <strong id="username">User</strong></p>
                <p class="mb-0" id="dateTimeDisplay"></p>
            </div>

            <!-- Profile Dropdown -->
            <div class="profile-dropdown dropdown">
                <img src="../../assets/img/md.png"
                    alt="Admin Profile"
                    class="dropdown-toggle"
                    id="profileDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                <!-- Dropdown menu -->
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="#" id="profileButton">Profile</a></li>
                    <li><a class="dropdown-item" href="#" id="settingsButton">Settings</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger logout-Button" href="#" id="logoutButton">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>


    <!-- Page Content -->
    <div class="container-fluid mt-4">

        <!-- Bootstrap Tab Navigation -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="patients-tab" data-bs-toggle="tab" data-bs-target="#patients" type="button" role="tab" aria-controls="patients" aria-selected="true">Patients Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab" aria-controls="schedule" aria-selected="false">Check-Up Schedule</button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="event-tab" data-bs-toggle="tab" data-bs-target="#event" type="button" role="tab" aria-controls="event" aria-selected="false">Event information</button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nutrition-report-tab" data-bs-toggle="tab" data-bs-target="#nutrition-report" type="button" role="tab" aria-controls="nutrition-report" aria-selected="false">Nutrition Report</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab" aria-controls="audit" aria-selected="false">Audit Trail</button>
            </li>
            <li class="nav-item" role="presentation" id="acc-reg-container">
                <button class="nav-link" id="acc-reg" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="false">Create Account</button>
                <!-- Sub-navigation -->
                <div class="sub-nav">
                    <button class="sub-nav-button" data-target="view-users">View Users</button>
                </div>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Patient Profile Section -->
            <div class="tab-pane fade show active" id="patients" role="tabpanel" aria-labelledby="patients-tab">
                <h2 class="mt-4">Patient Profile</h2>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Contact</th>
                            <th>Family Record</th>
                            <th>Parents' Occupation</th>
                            <th>Medical History</th>
                            <th>Restrictions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>John Doe</td>
                            <td>35</td>
                            <td>(555) 123-4567</td>
                            <td>Family: Father - John Doe Sr.</td>
                            <td>Father: Engineer<br>Mother: Teacher</td>
                            <td>Asthma, Hypertension</td>
                            <td>Low Salt Diet</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jane Smith</td>
                            <td>28</td>
                            <td>(555) 765-4321</td>
                            <td>Family: Mother - Emma Smith</td>
                            <td>Father: Business Owner<br>Mother: Doctor</td>
                            <td>None</td>
                            <td>None</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Check-Up Schedule Section -->
            <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                <h2 class="mt-4">Check-Up Schedule</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Doctor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>John Doe</td>
                            <td>2024-11-25</td>
                            <td>10:00 AM</td>
                            <td>Dr. Adams</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jane Smith</td>
                            <td>2024-11-26</td>
                            <td>11:30 AM</td>
                            <td>Dr. Baker</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Event information Section -->
            <div class="tab-pane fade" id="event" role="tabpanel" aria-labelledby="event-tab">
                <div id="eventFormContainer" class="container mt-4">
                    <!-- Event form will be loaded here -->
                </div>
            </div>
            <!-- Nutrition Report Section -->
            <div class="tab-pane fade" id="nutrition-report" role="tabpanel" aria-labelledby="nutrition-report-tab">
                <div class="container mt-4">
                    <h2>Nutrition Report - Barangay San Andres Centers</h2>

                    <!-- Bar Graph Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Current Nutrition Status by Center (Ages 0-14)</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="nutritionBarGraph"></canvas>
                        </div>
                    </div>

                    <!-- Line Graph Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Historical Nutrition Trends</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="nutritionLineGraph"></canvas>
                        </div>
                    </div>

                    <!-- Progress Details Section -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Progress Analysis by Age Group</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h5>Age Group: 0-4 years</h5>
                                    <ul class="list-group">
                                        <li class="list-group-item">Average Weight Improvement: +15%</li>
                                        <li class="list-group-item">Height Progress: On track</li>
                                        <li class="list-group-item">Nutrition Status: Good</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h5>Age Group: 5-9 years</h5>
                                    <ul class="list-group">
                                        <li class="list-group-item">Average Weight Improvement: +12%</li>
                                        <li class="list-group-item">Height Progress: Above average</li>
                                        <li class="list-group-item">Nutrition Status: Excellent</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h5>Age Group: 10-14 years</h5>
                                    <ul class="list-group">
                                        <li class="list-group-item">Average Weight Improvement: +10%</li>
                                        <li class="list-group-item">Height Progress: Normal</li>
                                        <li class="list-group-item">Nutrition Status: Good</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Section -->
            <div class="tab-pane fade" id="audit" role="tabpanel" aria-labelledby="audit-tab">
                <div class="container-fluid mt-4">
                    <h2>System Audit Trail</h2>

                    <!-- Filter Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form id="auditFilterForm" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Action Type</label>
                                    <select name="action" class="form-select">
                                        <option value="">All Actions</option>
                                        <option value="LOGIN">Login</option>
                                        <option value="LOGOUT">Logout</option>
                                        <option value="REGISTER">Register</option>
                                        <option value="CREATE">Create</option>
                                        <option value="UPDATE">Update</option>
                                        <option value="DELETE">Delete</option>
                                        <option value="VIEW">View</option>
                                        <option value="SYSTEM_CHANGE">System Change</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date From</label>
                                    <input type="date" name="date_from" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date To</label>
                                    <input type="date" name="date_to" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block w-100">Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Audit Trail Table -->
                    <div class="table-responsive">
                        <table id="auditTable" class="table table-striped display responsive nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Account Registration Section -->
            <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="acc-reg">
                <!-- Add Account Form -->
                <div id="add-account" class="sub-content" style="display: none;">
                    <div id="signupFormContainer" class="container mt-4">
                        <!-- Signup form will be loaded here -->
                    </div>
                </div>

                <!-- View Users Section -->
                <div id="viewer" class="sub-content" style="display: none;">
                    <div class="container mt-4">
                        <div id="UsersFormContainer">
                            <!-- Users table will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Scripts -->
    <script>
        $(document).ready(function() {
            // Update date and time
            function updateDateTime() {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                const dateTimeStr = now.toLocaleDateString('en-US', options);
                $('#dateTimeDisplay').text(dateTimeStr);
            }

            // Update time immediately and then every second
            updateDateTime();
            setInterval(updateDateTime, 1000);

            // Refresh table when viewing users
            $('.sub-nav-button[data-target="view-users"]').click(function() {
                $('.sub-content').hide();
                $('#viewer').show();

                // Load content without affecting other elements
                $('#UsersFormContainer').load('../../src/view/users.php', function() {});

                // Hide sub-nav after clicking
                $('.sub-nav').hide();
            });

            // Show sub-nav on hover
            $('#acc-reg-container').hover(
                function() {
                    $('.sub-nav').show();
                },
                function() {
                    if (!$('#viewer').is(':visible')) {
                        $('.sub-nav').hide();
                    }
                }
            );

            // Create Account tab click handler
            $('#acc-reg').on('click', function() {
                $('.sub-content').hide();
                $('#add-account').show();
                $('#signupFormContainer').load('/src/view/signup.php', function() {

                });
            });

            $('#event-tab').on('click', function() {
                $('#eventFormContainer').load('/src/view/event.php', function() {
                    // Optional callback for any post-load processing
                });
            });
            
            // Bar Graph Data
            const nutritionBarCtx = document.getElementById('nutritionBarGraph').getContext('2d');
            new Chart(nutritionBarCtx, {
                type: 'bar',
                data: {
                    labels: ['Center 1', 'Center 2', 'Center 3', 'Center 4', 'Center 5', 'Center 6'],
                    datasets: [{
                        label: 'Normal Weight %',
                        data: [75, 82, 78, 85, 80, 77],
                        backgroundColor: 'rgba(75, 192, 192, 0.6)'
                    }, {
                        label: 'Underweight %',
                        data: [15, 10, 12, 8, 12, 13],
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    }, {
                        label: 'Overweight %',
                        data: [10, 8, 10, 7, 8, 10],
                        backgroundColor: 'rgba(255, 206, 86, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });

            // Line Graph Data
            const nutritionLineCtx = document.getElementById('nutritionLineGraph').getContext('2d');
            new Chart(nutritionLineCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: '0-4 years',
                        data: [65, 70, 75, 78, 82, 85],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        tension: 0.1
                    }, {
                        label: '5-9 years',
                        data: [70, 72, 76, 80, 83, 85],
                        borderColor: 'rgba(255, 99, 132, 1)',
                        tension: 0.1
                    }, {
                        label: '10-14 years',
                        data: [75, 77, 80, 82, 85, 87],
                        borderColor: 'rgba(255, 206, 86, 1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 50,
                            max: 100
                        }
                    }
                }
            });
        });
    </script>
    <script src="/src/script/dropdrown.js"></script>
    <script src="/src/script/logout.js"></script>

    <script src="/src/script/audit_trail.js"></script>

</body>

</html>