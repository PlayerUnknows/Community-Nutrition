<?php
require_once __DIR__ . '/../models/MonitoringModel.php';
?>

<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-chart-line me-1"></i>
                Nutrition Monitoring Records
            </div>
            <div>
                <button type="button" class="btn btn-primary me-2" id="exportMonitoringBtn">
                    <i class="fas fa-file-export"></i> Export Data
                </button>
                <button type="button" class="btn btn-success" id="nutritionReportBtn" onclick="window.location.href='report.php'">
                    <i class="fas fa-chart-bar"></i> Nutrition Report
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="monitoringTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Family ID</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Weight (kg)</th>
                            <th>Height (cm)</th>
                            <th>BP</th>
                            <th>Temperature</th>
                            <th>Weight Category</th>
                            <th>BMI Status</th>
                            <th>Growth Status</th>
                            <th>Arm Circumference</th>
                            <th>Arm Status</th>
                            <th>Findings</th>
                            <th>Appointment Date</th>
                            <th>Time</th>
                            <th>Place</th>
                            <th>Created At</th>
                            <th>Actions</th>
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

<!-- View Details Modal -->
<div class="modal fade" id="viewMonitoringModal" tabindex="-1" aria-labelledby="viewMonitoringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMonitoringModalLabel">Monitoring Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Patient Information</h6>
                        <p><strong>Patient ID:</strong> <span id="view-patient-id"></span></p>
                        <p><strong>Family ID:</strong> <span id="view-family-id"></span></p>
                        <p><strong>Age:</strong> <span id="view-age"></span></p>
                        <p><strong>Sex:</strong> <span id="view-sex"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Vital Signs</h6>
                        <p><strong>Weight:</strong> <span id="view-weight"></span> kg</p>
                        <p><strong>Height:</strong> <span id="view-height"></span> cm</p>
                        <p><strong>BP:</strong> <span id="view-bp"></span></p>
                        <p><strong>Temperature:</strong> <span id="view-temperature"></span>°C</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Assessment</h6>
                        <p><strong>Weight Category:</strong> <span id="view-weight-category"></span></p>
                        <p><strong>BMI Status:</strong> <span id="view-bmi"></span></p>
                        <p><strong>Growth Status:</strong> <span id="view-growth"></span></p>
                        <p><strong>Arm Circumference:</strong> <span id="view-arm"></span></p>
                        <p><strong>Arm Status:</strong> <span id="view-arm-status"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Appointment Details</h6>
                        <p><strong>Date:</strong> <span id="view-date"></span></p>
                        <p><strong>Time:</strong> <span id="view-time"></span></p>
                        <p><strong>Place:</strong> <span id="view-place"></span></p>
                        <p><strong>Created At:</strong> <span id="view-created"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Findings</h6>
                        <p id="view-findings"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
