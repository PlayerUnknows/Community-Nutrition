<?php
require_once __DIR__ . '/../controllers/ReportController.php';
?>

<div class="container-fluid mt-4">
    <!-- Date Filter Section -->
    <div class="row mb-4">
        <div class="col-12 col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Date Filter</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="startDate" class="form-label">Start Date:</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-5">
                            <label for="endDate" class="form-label">End Date:</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="filterDates">Filter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-weight"></i> BMI Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="bmiDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this section for BMI Category Distribution -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> BMI Category Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="bmiCategoryTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>BMI Category</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table"></i> BMI Distribution Details</h5>
                <div class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text">Show</span>
                        <select id="entriesSelect" class="form-select" style="width: 70px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                        <span class="input-group-text">entries</span>
                    </div>
                    <div class="input-group">
                        <input type="text" id="dateRangePicker" class="form-control" placeholder="Select date range">
                        <button class="btn btn-primary" id="applyDateRange">
                            <i class="fas fa-check"></i> Apply
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="min-height: 500px;">
                    <table id="bmiTable" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient ID</th>
                                <th>Age</th>
                                <th>BMI Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add these CSS styles -->
<style>
.card-body {
    padding: 1.25rem;
}

.table-responsive {
    margin: 0;
    padding: 0;
}

#bmiTable {
    margin: 0 !important;
}

.dataTables_wrapper .dataTables_length, 
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

.daterangepicker {
    z-index: 1100;
}
</style>

<!-- Load dependencies in correct order -->
<!-- jQuery first -->
<script src="../../node_modules/jquery/dist/jquery.min.js"></script>

<!-- Bootstrap dependencies -->
<script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
<script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>

<!-- Moment.js before daterangepicker -->
<script src="../../node_modules/moment/moment.js"></script>

<!-- DateRangePicker after moment -->
<link rel="stylesheet" type="text/css" href="../../node_modules/daterangepicker/daterangepicker.css" />
<script src="../../node_modules/daterangepicker/daterangepicker.js"></script>

<!-- Chart.js -->
<script src="../../node_modules/chart.js/dist/chart.umd.js"></script>

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
<script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>

<!-- Custom Script -->
<script src="../../src/script/bmi_statistics.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Clear canvas when the component is loaded
        const canvas = document.getElementById('bmiDistributionChart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    });
</script>