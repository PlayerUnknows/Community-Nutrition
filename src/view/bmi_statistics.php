<?php
require_once __DIR__ . '/../controllers/ReportController.php';
?>

<div class="container-fluid mt-4">
    <!-- Date Filter Section -->
    

    <!-- Chart Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-weight"></i> BMI Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" id="overallDateRange" class="form-control" placeholder="Select date range">
                            <button class="btn btn-success" id="applyOverallDateRange">
                                <i class="fas fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="bmiDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gender-specific BMI Distribution Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-pink text-white">
                    <h5 class="mb-0"><i class="fas fa-venus"></i> Female BMI Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" id="femaleDateRange" class="form-control" placeholder="Select date range">
                            <button class="btn btn-pink" id="applyFemaleDateRange">
                                <i class="fas fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="femaleBmiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-blue text-white">
                    <h5 class="mb-0"><i class="fas fa-mars"></i> Male BMI Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" id="maleDateRange" class="form-control" placeholder="Select date range">
                            <button class="btn btn-blue" id="applyMaleDateRange">
                                <i class="fas fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="maleBmiChart"></canvas>
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

<!-- Only keep the specific styles for this component -->
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

/* Add custom background colors for gender charts */
.bg-pink {
    background-color: #FF69B4 !important;
}

.bg-blue {
    background-color: #4169E1 !important;
}

/* Set fixed height for chart containers */
.chart-container {
    position: relative;
    height: 300px !important;
    width: 100%;
    margin: 0 auto;
}

/* Ensure proper padding and margins for cards */
.card {
    margin-bottom: 1rem;
}

.card-body {
    padding: 1.25rem;
    background-color: #fff;
}

/* Style date range inputs and buttons */
.input-group {
    max-width: 400px;
}

.btn-pink {
    background-color: #FF69B4;
    border-color: #FF69B4;
    color: white;
}

.btn-pink:hover {
    background-color: #FF1493;
    border-color: #FF1493;
    color: white;
}

.btn-blue {
    background-color: #4169E1;
    border-color: #4169E1;
    color: white;
}

.btn-blue:hover {
    background-color: #0000CD;
    border-color: #0000CD;
    color: white;
}

/* Style daterangepicker dropdown */
.daterangepicker {
    z-index: 3000;
}

.daterangepicker td.active {
    background-color: #4169E1;
}

.daterangepicker td.active:hover {
    background-color: #0000CD;
}
</style>

<!-- Load dependencies in correct order -->
<script src="../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
<script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../../node_modules/moment/moment.js"></script>

<!-- DataTables -->
<script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

<!-- Other dependencies -->
<script src="../../node_modules/daterangepicker/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../../node_modules/daterangepicker/daterangepicker.css" />
<script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
<script src="../../node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>

<!-- Initialize your custom script after all dependencies are loaded -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure all required libraries are loaded
    const checkDependencies = () => {
        return typeof $ !== 'undefined' && 
               typeof $.fn.DataTable !== 'undefined' && 
               typeof $.fn.daterangepicker !== 'undefined' &&
               typeof Chart !== 'undefined';
    };

    const initializeScripts = () => {
        if (!checkDependencies()) {
            setTimeout(initializeScripts, 100);
            return;
        }
        
        // Load your custom script
        const script = document.createElement('script');
        script.src = '../script/bmi_statistics.js';
        document.body.appendChild(script);
    };

    initializeScripts();
});
</script>
