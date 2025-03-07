<?php
require '../../vendor/autoload.php';

// use Katzgrau\KLogger\Logger;
// use Psr\Log\LogLevel;



session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css">
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

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
            background-color: var(--primary-blue) !important;
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

        /*#signupFormContainer .signup-container {
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
        }*/

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

        /* Adjustments for smaller screens */
        .header-section {
            padding: 0.25rem 0;
            /* Further reduced padding */
        }

        .header-section .responsive-logo {
            width: 40px;
            /* Smaller logo size */
            height: auto;
        }

        .header-section .text-center p {
            font-size: 0.8rem;
            /* Smaller font size */
            margin-bottom: 0;
            /* Remove bottom margin */
        }

        .header-section .profile-dropdown img {
            width: 30px;
            /* Smaller profile image size */
            height: auto;
        }
    </style>
</head>

<body>

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
                            $displayEmail = htmlspecialchars($_SESSION['email']);
                        } elseif (isset($_SESSION['user']['email']) && !empty($_SESSION['user']['email'])) {
                            $displayEmail = htmlspecialchars($_SESSION['user']['email']);
                        }

                        echo $displayEmail;
                        ?>
                    </strong></p>
                <p class="mb-0" id="dateTimeDisplay"></p>
            </div>
            <!-- Profile Dropdown -->
            <div class="profile-dropdown dropdown">
                <img src="../../assets/img/dummy-profile.png"
                    alt="Admin Profile"
                    class="dropdown-toggle"
                    id="profileDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="width: 65px; height: 65px;">

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
                <button class="nav-link active" id="patients-tab" data-bs-toggle="tab" data-bs-target="#patients" type="button" role="tab" aria-controls="patients" aria-selected="true" tabindex="0">Patients Profile</button>
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

        <div class="tab-content" id="myTabContent" role="tabpanel">
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

            <!-- Nutrition Monitoring Section -->
            <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                <div class="container mt-4">
                    <!-- Monitoring Records Content -->
                    <div id="monitoring-records" class="sub-content">
                        <h2>Nutrition Monitoring Records</h2>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3>Nutrition Check-up History</h3>
                                    <div class="col-md-4">
                                        <input type="text" id="historySearch" class="form-control form-control-sm" placeholder="Search nutrition history...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Accompanied By</th>
                                        <th>Weight (kg)</th>
                                        <th>Height (cm)</th>
                                        <th>Weight-for-Age</th>
                                        <th>Height-for-Age</th>
                                        <th>Weight-for-Height</th>
                                        <th>Last Check-up</th>
                                        <th>Nutritional Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>P001</td>
                                        <td>Juan Dela Cruz</td>
                                        <td>2 yrs</td>
                                        <td>Maria Dela Cruz (Mother)</td>
                                        <td>12.5</td>
                                        <td>86</td>
                                        <td>Normal</td>
                                        <td>Normal</td>
                                        <td>Normal</td>
                                        <td>2024-12-01</td>
                                        <td>Well-nourished</td>
                                    </tr>
                                    <tr>
                                        <td>P002</td>
                                        <td>Maria Santos</td>
                                        <td>5 yrs</td>
                                        <td>Roberto Santos (Father)</td>
                                        <td>15.2</td>
                                        <td>105</td>
                                        <td>Underweight</td>
                                        <td>Stunted</td>
                                        <td>Wasted</td>
                                        <td>2024-12-02</td>
                                        <td>Needs intervention</td>
                                    </tr>
                                    <tr>
                                        <td>P003</td>
                                        <td>Pedro Reyes</td>
                                        <td>8 mos</td>
                                        <td>Ana Reyes (Mother)</td>
                                        <td>8.3</td>
                                        <td>70</td>
                                        <td>Normal</td>
                                        <td>Normal</td>
                                        <td>Normal</td>
                                        <td>2024-12-03</td>
                                        <td>Well-nourished</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Add JavaScript for search functionality for check-ups and history tables -->
                        <script>
                            document.getElementById('historySearch').addEventListener('keyup', function() {
                                searchTable('historyTable', this.value);
                            });

                            function searchTable(tableId, searchText) {
                                const table = document.getElementById(tableId);
                                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                                searchText = searchText.toLowerCase();
                                for (let row of rows) {
                                    let text = row.textContent || row.innerText;
                                    text = text.toLowerCase();
                                    row.style.display = text.includes(searchText) ? '' : 'none';
                                }
                            }
                        </script>
                    </div>

                    <!-- Nutrition Report Content -->
                    <div id="nutrition-report" class="sub-content" style="display: none;">
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
                <div class="container mt-4">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <?php include 'audit_trail.php'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Registration Section -->
            <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="acc-reg">
                <!-- Add Account Form -->
                <div id="add-account" class="sub-content" style="display: none;">
                    <div id="signupFormContainer" class="container mt-4">
                        <!-- Signup form will be loaded here -->
                        <?php include 'signup.php'; ?>
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
  
    <script src="/src/script/dropdrown.js"></script>
    <script>
        $(document).ready(function() {
            // Track loaded state for each tab
            const loadedTabs = {
                patients: false,
                monitoring: false,
                'nutrition-report': false,
                appointments: false
            };

            // Handle sub-navigation clicks
            $('.sub-nav .dropdown-item').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Remove active class from all tabs
                $('.nav-link').removeClass('active');
                
                // Add active class to parent schedule tab
                $('#schedule-tab').addClass('active');
                
                // Show the target tab content
                const targetId = $(this).attr('data-bs-target');
                $('.tab-pane').removeClass('show active');
                $(targetId).addClass('show active');
            });

            // Load patient profile content immediately on page load
            if (!loadedTabs.patients) {
                $('#patientProfileContainer').load('/src/view/patient_profile.php', function() {
                    loadedTabs.patients = true;
                });
            }

            // Load signup form content once when the document is ready
            if (!loadedTabs.account) {
                $('#signupFormContainer').load('/src/view/signup.php', function() {
                    loadedTabs.account = true;
                });
            }

            // Event tab click handler
            $('#event-tab').on('click', function() {
                if (!loadedTabs.event) {
                    $('#eventFormContainer').load('/src/view/event.php', function() {
                        loadedTabs.event = true;
                    });
                }
            });

            // Audit trail tab click handler
            $('#audit-tab').on('click', function() {
                if (!loadedTabs.audit) {
                    $('.container', '#audit').load('/src/view/audit_trail.php', function() {
                        loadedTabs.audit = true;
                    });
                }
            });

            // Refresh table only when viewing users for the first time
            $('.sub-nav-button[data-target="view-users"]').click(function() {
                $('.sub-content').hide();
                $('#viewer').show();

                if (!loadedTabs.users) {
                    $('#UsersFormContainer').load('../../src/view/users.php', function() {
                        loadedTabs.users = true;
                    });
                }

                // Hide sub-nav after clicking
                $('.sub-nav').hide();
            });

            // Create Account tab click handler
            $('#acc-reg').on('click', function() {
                $('.sub-content').hide();
                $('#add-account').show();
            });

            // Show sub-nav on hover
            $('#acc-reg-container').hover(
                function() {
                    $(this).find('.sub-nav').show();
                },
                function() {
                    if (!$('#viewer').is(':visible')) {
                        $(this).find('.sub-nav').hide();
                    }
                }
            );

            // Handle sub-navigation clicks for monitoring
            $('#monitoring-container').hover(
                function() {
                    $(this).find('.sub-nav').show();
                },
                function() {
                    if (!$('#nutrition-report').is(':visible')) {
                        $(this).find('.sub-nav').hide();
                    }
                }
            );

            // Show monitoring records by default when clicking main tab
            $('#schedule-tab').on('click', function() {
                $('.sub-content').hide();
                $('#monitoring-records').show();
            });

            // Handle nutrition report button click
            $('.sub-nav-button[data-target="nutrition-report"]').click(function() {
                $('.sub-content').hide();
                $('#nutrition-report').show();
                $(this).closest('.sub-nav').hide();
            });

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

            // Modal accessibility improvements
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                // Store the element that triggered the modal
                let triggerElement = null;
                const modalInstance = new bootstrap.Modal(modal);

                // Function to properly hide modal and clean up
                function hideModalAndCleanup() {
                    modalInstance.hide();
                    // Remove backdrop
                    const backdrops = document.getElementsByClassName('modal-backdrop');
                    while (backdrops.length > 0) {
                        backdrops[0].remove();
                    }
                    // Clean up body classes
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    // Reset modal state
                    modal.style.display = 'none';
                    modal.setAttribute('aria-hidden', 'true');
                    modal.setAttribute('inert', '');
                }

                // When modal is about to be shown
                modal.addEventListener('show.bs.modal', function () {
                    this.removeAttribute('aria-hidden');
                    triggerElement = document.activeElement;
                    this.removeAttribute('inert');
                });

                // When modal is hidden
                modal.addEventListener('hidden.bs.modal', function () {
                    if (triggerElement) {
                        triggerElement.focus();
                    }
                    hideModalAndCleanup();
                });

                // Add cleanup to all close buttons in this modal
                const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
                closeButtons.forEach(button => {
                    button.addEventListener('click', hideModalAndCleanup);
                });

                // Cleanup after successful transactions
                modal.addEventListener('transactionComplete', function() {
                    hideModalAndCleanup();
                });

                // Trap focus within modal when open
                modal.addEventListener('keydown', function (e) {
                    if (e.key === 'Tab') {
                        const focusableElements = modal.querySelectorAll(
                            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                        );
                        const firstFocusable = focusableElements[0];
                        const lastFocusable = focusableElements[focusableElements.length - 1];

                        if (e.shiftKey) {
                            if (document.activeElement === firstFocusable) {
                                lastFocusable.focus();
                                e.preventDefault();
                            }
                        } else {
                            if (document.activeElement === lastFocusable) {
                                firstFocusable.focus();
                                e.preventDefault();
                            }
                        }
                    }
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

    <script src="/src/script/loader.js"></script>

</body>

</html>