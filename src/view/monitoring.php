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
            </div>
        </div>
        <div class="card-body">
            <!-- Custom Controls -->
            <div class="row mb-3">
                <div class="col-md-6 d-flex align-items-center">
                    <label class="me-2">Show entries:</label>
                    <select id="monitoringLength" class="form-select" style="width: auto;">
                    <option value="5">5</option>    
                    <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <input type="search" id="monitoringSearch" class="form-control" style="width: 200px;" placeholder="Search...">
                    </div>
                </div>
            </div>

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

            <!-- Custom Pagination -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="monitoringInfo" class="dataTables_info"></div>
                </div>
                <div class="col-md-6">
                    <div class="dataTables_paginate paging_simple_numbers" id="monitoringPagination">
                        <ul class="pagination justify-content-end">
                            <!-- Pagination will be dynamically populated -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>