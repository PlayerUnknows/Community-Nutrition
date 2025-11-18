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
