<?php 
require_once __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css">

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
            background-color: rgba(255, 255, 255, 0.8); /* transparent white background */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-logo {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
        }

        .loading-dots {
            display: flex;
            gap: 8px;
        }

        .dot {
            width: 12px;
            height: 12px;
            background-color: #007bff;
            border-radius: 50%;
            animation: bounce 0.5s ease-in-out infinite;
        }

        .dot:nth-child(2) {
            animation-delay: 0.1s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.2s;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body>
    <!-- Update loading screen div -->
    <div id="loading-screen">
        <img src="../../assets/img/SanAndres.svg" alt="Logo" class="loading-logo">
        <div class="loading-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
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
            <li class="nav-item" role="presentation" id="monitoring-container">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab" aria-controls="schedule" aria-selected="false" tabindex="-1">Nutrition Monitoring</button>
                <div class="sub-nav">
                    <button class="sub-nav-button" data-target="nutrition-report">Nutrition Report</button>
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

        <div class="tab-content" id="myTabContent" role="tabpanel">
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
                    <div id="auditTrailContainer">
                        <!-- Audit trail content will be loaded here -->
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
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/moment/moment.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

    <!-- Custom Scripts -->
 
    <script src="../../src/script/audit_trail.js"></script>
    <script src="../../src/script/appointments.js"></script>
    <script src="../../src/script/users.js"></script>
    <script src="../../src/script/dropdrown.js"></script>

    <script>
        $(document).ready(function() {
            // Track loaded state for each tab
            const loadedTabs = {
                patients: false,
                monitoring: false,
                appointments: false,
                event: false,
                audit: false,
                account: false
            };

            let usersScriptLoaded = false;

            // Function to load users content
            function loadUsersContent() {
                if (!loadedTabs.account) {
                    $('#UsersFormContainer').load('users.php', function() {
                        if (!usersScriptLoaded) {
                            const script = document.createElement('script');
                            script.src = '../script/users.js';
                            script.onload = function() {
                                usersScriptLoaded = true;
                            };
                            document.body.appendChild(script);
                        }
                        loadedTabs.account = true;
                    });
                }
            }

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

                if (!loadedTabs.account) {
                    $('#UsersFormContainer').load('../../src/view/users.php', function() {
                        loadedTabs.account = true;
                    });
                }

                // Hide sub-nav after clicking
                $('.sub-nav').hide();
            });

            // Handle account tab clicks
            $('#acc-reg').on('click', function() {
                const currentContent = $('#account .sub-content:visible').attr('id');

                if (currentContent === 'add-account') {
                    $('#add-account').hide();
                    $('#viewer').show();
                    loadUsersContent();
                } else {
                    $('#viewer').hide();
                    $('#add-account').show();
                }
            });

            // Handle view users button click
            $('#view-users-btn').on('click', function() {
                $('#add-account').hide();
                $('#viewer').show();
                loadUsersContent();
            });

            // Handle create account button click
            $('#create-account-btn').on('click', function() {
                $('#viewer').hide();
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
                $('#schedule .sub-content').hide();
                $('#monitoring-records').show();
            });

            // Also handle the tab shown event
            $('#schedule-tab').on('shown.bs.tab', function() {
                $('#schedule .sub-content').hide();
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
                modal.addEventListener('show.bs.modal', function() {
                    this.removeAttribute('aria-hidden');
                    triggerElement = document.activeElement;
                    this.removeAttribute('inert');
                });

                // When modal is hidden
                modal.addEventListener('hidden.bs.modal', function() {
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
                modal.addEventListener('keydown', function(e) {
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

            // Handle nutrition monitoring sub-content switching
            $('#schedule .btn-group .btn').click(function() {
                // Remove active class from all buttons
                $(this).siblings().removeClass('active');
                // Add active class to clicked button
                $(this).addClass('active');

                // Hide all sub-content
                $('.sub-content').hide();
                // Show the selected content
                $('#' + $(this).data('content')).show();
            });

            // Initialize Nutrition Bar Chart
            const nutritionBarChart = document.getElementById('nutritionBarChart');
            if (nutritionBarChart) {
                const nutritionBarCtx = nutritionBarChart.getContext('2d');
                new Chart(nutritionBarCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Center 1', 'Center 2', 'Center 3', 'Center 4', 'Center 5', 'Center 6'],
                        datasets: [{
                            label: 'Number of Patients',
                            data: [65, 59, 80, 81, 56, 55],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(255, 206, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(255, 159, 64, 0.2)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Patient Distribution Across Centers'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>


    <!-- <script src="../../src/script/loader.js"></script> -->

    <script>
        $(document).ready(function() {
            // Handle sub-navigation for Nutrition Monitoring
            $('#monitoring-container .sub-nav-button').click(function(e) {
                e.preventDefault();
                const target = $(this).data('target');
                $('#schedule .sub-content').hide();
                $(`#${target}`).show();
            });

            // Show monitoring records by default when Nutrition Monitoring tab is clicked
            $('#schedule-tab').on('click', function() {
                $('#schedule .sub-content').hide();
                $('#monitoring-records').show();
            });

            // Also handle the tab shown event
            $('#schedule-tab').on('shown.bs.tab', function() {
                $('#schedule .sub-content').hide();
                $('#monitoring-records').show();
            });

            // Show sub-nav on hover for monitoring tab
            $('#monitoring-container').hover(
                function() {
                    $(this).find('.sub-nav').show();
                },
                function() {
                    $(this).find('.sub-nav').hide();
                }
            );

            // Handle sub-navigation for Account Registration
            $('.sub-nav-button').click(function(e) {
                e.preventDefault();
                const target = $(this).data('target');
                $('.sub-content').hide();
                $(`#${target}`).show();

                // Initialize DataTable when switching to users view
                if (target === 'view-users') {
                    if (typeof loadUsers === 'function') {
                        loadUsers();
                    }
                }
            });

            // Show signup form by default when Create Account tab is clicked
            $('#acc-reg').on('click', function() {
                $('.sub-content').hide();
                $('#signupFormContainer').show();
            });

            // Also handle the tab shown event
            $('#acc-reg').on('shown.bs.tab', function() {
                $('.sub-content').hide();
                $('#signupFormContainer').show();
            });

            // Initialize with signup form visible if account tab is active
            if ($('#acc-reg').hasClass('active')) {
                $('.sub-content').hide();
                $('#signupFormContainer').show();
            }

            // Initialize dropdown functionality
            $('.nav-item.dropdown').hover(
                function() {
                    $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(200);
                },
                function() {
                    $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(200);
                }
            );
        });
    </script>
    <script src="../../src/script/logout.js"></script>
    <script src="../../src/script/session.js"></script>

    <script>
        // Add this loading screen script right before the closing body tag
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loading-screen').style.display = 'none';
            }, 3000);
        });
    </script>
</body>

</html>