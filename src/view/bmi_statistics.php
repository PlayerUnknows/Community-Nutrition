<div class="container-fluid mt-4">
    <!-- Date Filter Section -->
    
    <!-- Export buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-eye"></i> Preview Report
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-preview="bmi-distribution">BMI Distribution</a></li>
                    <li><a class="dropdown-item" href="#" data-preview="female-bmi">Female BMI Distribution</a></li>
                    <li><a class="dropdown-item" href="#" data-preview="male-bmi">Male BMI Distribution</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-preview="bmi-category">BMI Category Distribution</a></li>
                    <li><a class="dropdown-item" href="#" data-preview="bmi-table">BMI Distribution Details</a></li>
                </ul>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-export="excel" data-type="bmi-category">BMI Category Distribution</a></li>
                    <li><a class="dropdown-item" href="#" data-export="excel" data-type="bmi-table">BMI History</a></li>
                </ul>
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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="bmiDateRange" class="form-control" placeholder="Select date range">
                                <button class="btn btn-warning" id="applyBmiDateRange">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                            </div>
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
                        <table id="bmiCategoryTable" class="table table-striped table-bordered w-100">
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

<!-- BMI Statistics Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-table"></i> BMI History</h5>
            </div>
            <div class="card-body">
                <!-- Search and Entries Controls -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Show entries on the left -->
                            <div class="d-flex align-items-center">
                                <label class="me-2">Show</label>
                                <select id="bmiTableEntriesSelect" class="form-select form-select-sm" style="width: 70px;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="-1">All</option>
                                </select>
                                <label class="ms-2">entries</label>
                            </div>

                            <!-- Search bar on the right with only search icon -->
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <input type="text" id="bmiTableSearch" class="form-control form-control-sm" placeholder="Search records...">
                                <span class="input-group-text border-start-0">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table id="bmiTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Height (cm)</th>
                                <th>Weight (kg)</th>
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
            width: 100%;
            overflow-x: auto;
        }

#bmiTable {
    margin: 0 !important;
    width: 100% !important;
    white-space: nowrap;
}

#bmiTable th {
    padding: 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* DataTables Responsive styles */
table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control {
    padding-left: 30px !important;
}

table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
    top: 50%;
    transform: translateY(-50%);
    left: 5px;
    height: 14px;
    width: 14px;
    border-radius: 14px;
    line-height: 14px;
    text-align: center;
    color: white;
    background-color: #0d6efd;
}

/* Table wrapper adjustments */
.dataTables_wrapper {
    padding: 0 !important;
    margin: 0 !important;
}

.dataTables_wrapper .row {
    margin: 0;
    padding: 10px 0;
}

/* Mobile optimizations */
@media screen and (max-width: 767px) {
    #bmiTable_wrapper .row:first-child {
        flex-direction: column;
    }

    #bmiTable_wrapper .col-sm-12 {
        width: 100%;
        margin-bottom: 10px;
    }

    .dataTables_filter {
        text-align: left !important;
    }
}

/* Entries and date range controls */
.d-flex.gap-2 {
    display: flex;
    align-items: center;
    gap: 1rem !important;
}

.input-group {
    background: white;
    border-radius: 4px;
    overflow: hidden;
}

.input-group-text {
    background: #f8f9fa;
    border: none;
    color: #6c757d;
}

.form-select, 
.form-control {
    border: none;
    box-shadow: none !important;
}

.btn-primary {
    background: #0d6efd;
    border: none;
    padding: 0.5rem 1rem;
}

.btn-primary:hover {
    background: #0b5ed7;
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

/* Add these styles to your existing styles section */
.status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.9em;
    display: inline-block;
    text-align: center;
    min-width: 100px;
}

.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: black !important;
}

.bg-success {
    background-color: #28a745 !important;
    color: white !important;
}

/* Ensure text is visible on all badges */
.status-badge.bg-warning {
    color: black !important;
}

.status-badge.bg-danger,
.status-badge.bg-success {
    color: white !important;
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
<script src="../../node_modules/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>

<link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">
<link rel="stylesheet" type="text/css" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="../../node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css">

<!-- Other dependencies -->
<script src="../../node_modules/daterangepicker/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../../node_modules/daterangepicker/daterangepicker.css" />
<script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
<script src="../../node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>



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
