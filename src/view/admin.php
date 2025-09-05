<?php
require_once __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . '/../includes/head_nodes_module.php'; ?>
</head>

<body>
    <!-- Update the loading screen div -->
    <div id="loading-screen">
        <img src="../../assets/img/SanAndres.svg" alt="Logo" class="loading-logo">
        <div class="loading-spinner"></div>
    </div>
    <!-- Header Section -->
    <?php include __DIR__ . '/../includes/header_section.php'; ?>

    <!-- Page Content -->
    <div class="container-fluid mt-4">
        <!-- Navigation Tabs -->
        <?php include __DIR__ . '/../includes/navigation.php'; ?>

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
            <?php include 'audit_trail.php'; ?>

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
    <?php include __DIR__ . '/../includes/footer_nodes_module.php'; ?>
</body>

</html>