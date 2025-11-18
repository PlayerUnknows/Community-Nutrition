<?php
require_once __DIR__ . '/../controllers/ReportController.php';

// Helper functions for status and calculations



$reportController = new ReportController();
$reportData = $reportController->generateReport();

if (isset($reportData['error'])) {
    echo "<div class='alert alert-danger'>{$reportData['error']}</div>";
    exit;
}

$records = $reportData['growthTrends'];
$nutritionalStatus = $reportData['nutritionalStatus'];
$ageGroupAnalysis = $reportData['ageGroupAnalysis'];

// Process data for charts
$dates = [];
$weights = [];
$heights = [];
$bmis = [];
$armCircumferences = [];

foreach ($records as $record) {
    $dates[] = $record['created_at'];
    $weights[] = floatval($record['weight']);
    $heights[] = floatval($record['height']);
    $heightInMeters = $record['height'] / 100;
    $bmis[] = round($record['weight'] / ($heightInMeters * $heightInMeters), 2);
    $armCircumferences[] = floatval($record['arm_circumference']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition Analytics Report</title>
    <!-- Bootstrap CSS -->
    <link href="../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../node_modules/daterangepicker/daterangepicker.css" />
    <!-- Custom CSS -->
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .status-normal {
            background-color: #28a745;
            color: white;
        }

        .status-warning {
            background-color: #ffc107;
            color: black;
        }

        .status-alert {
            background-color: #dc3545;
            color: white;
        }

        .bg-pink {
            background-color: #FF69B4 !important;
            color: white;
        }

        .badge {
            font-size: 0.875rem;
            padding: 0.4em 0.8em;
        }

        .search-container {
            max-width: 200px;
            position: relative;
        }

        #heightTableSearch {
            padding-right: 30px;
        }

        .search-container .fas.fa-search {
            font-size: 0.875rem;
        }

        .input-group-text {
            background-color: #fff;
            border-left: none;
        }

        #heightTableSearch {
            border-right: none;
        }

        #heightTableSearch:focus+.input-group-text {
            border-color: #86b7fe;
        }

        .fa-search {
            color: #6c757d;
        }

        #heightTableSearch {
            border-radius: 4px;
        }

        #clearSearch {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        #clearSearch:hover {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12 mb-4">
                <h2><i class="fas fa-chart-line"></i> Nutrition Analytics Report</h2>
            </div>
        </div>

        <!-- Export buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-eye"></i> Preview Report
                    </button>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Growth Status</h6></li>
                        <li><a class="dropdown-item growth-preview-link" href="#" data-type="growth-chart">Growth Status Distribution</a></li>
                        <li><a class="dropdown-item growth-preview-link" href="#" data-type="growth-summary">Growth Status Summary</a></li>
                        <li><a class="dropdown-item growth-preview-link" href="#" data-type="growth-statistics">Growth Statistics by Age & Gender</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Gender Analysis</h6></li>
                        <li><a class="dropdown-item growth-preview-link" href="#" data-type="gender-distribution">Gender Distribution</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Raw Data</h6></li>
                        <li><a class="dropdown-item growth-preview-link" href="#" data-type="height-measurements">Height Measurements History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item growth-preview-link" href="#" data-type="all">Complete Report</a></li>
                    </ul>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Growth Status</h6></li>
                        <li><a class="dropdown-item" href="#" data-export="excel" data-type="growth-summary">Growth Status Summary</a></li>
                        <li><a class="dropdown-item" href="#" data-export="excel" data-type="growth-statistics">Growth Statistics by Age & Gender</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Gender Analysis</h6></li>
                        <li><a class="dropdown-item" href="#" data-export="excel" data-type="gender-distribution">Gender Distribution</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Raw Data</h6></li>
                        <li><a class="dropdown-item" href="#" data-export="excel" data-type="height-measurements">Height Measurements History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" data-export="excel" data-type="all">Complete Report</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Growth Trends -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Growth Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="growthStatusDateRange" class="form-control" placeholder="Select date range">
                                <button class="btn btn-primary" id="applyGrowthStatusDateRange">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="growthTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div id="growthStatusSummaryContainer">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-table"></i> Growth Status Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody id="growthStatusSummary">
                                        <!-- Data will be populated by JavaScript -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td>Total</td>
                                            <td id="totalCount">0</td>
                                            <td>100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Growth Statistics by Age & Gender moved below -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Growth Statistics by Age & Gender</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered growth-stats-table">
                                <thead>
                                    <tr>
                                        <th>Age Group</th>
                                        <th>Gender</th>
                                        <th>Avg Height (cm)</th>
                                        <th>Total Patients</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="3">Overall Total</td>
                                        <td id="growthStatsTotal">0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gender Distribution Chart -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Gender Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 300px;">
                            <canvas id="genderDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div id="genderDistributionSummaryContainer">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Gender Distribution Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Gender</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody id="genderDistributionSummary">
                                        <!-- Data will be populated by JavaScript -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active fw-bold">
                                            <td>Total</td>
                                            <td id="genderDistributionTotal">0</td>
                                            <td>100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BMI and Arm Circumference -->
        <div class="row mb-4">
            <!-- Remove the entire arm circumference div and rename this section appropriately -->
        </div>

        <!-- Measurements History Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Height Measurements History</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and Entries Controls -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <!-- Show entries on the left -->
                                    <div class="d-flex align-items-center">
                                        <label class="me-2">Show</label>
                                        <select id="heightTableEntriesSelect" class="form-select form-select-sm" style="width: auto;">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="-1">All</option>
                                        </select>
                                        <label class="ms-2">entries</label>
                                    </div>

                                    <!-- Search bar on the right -->
                                    <div class="d-flex align-items-center">
                                        <input type="text" id="heightTableSearch" class="form-control form-control-sm" placeholder="Search records..." style="width: 200px;">
                                        <button class="btn btn-sm btn-outline-secondary ms-1" id="clearSearch" type="button">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="measurementsTable" class="table table-striped table-bordered display nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient Name</th>
                                        <th>Age</th>
                                        <th>Sex</th>
                                        <th>Current Height (cm)</th>
                                        <th>Ideal Height (cm)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by DataTables -->
                                </tbody>
                            </table>
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
    <script src="../../node_modules/moment/moment.js"></script>
    <script src="../../node_modules/daterangepicker/daterangepicker.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/chart.js/dist/chart.umd.js"></script>

    <!-- Initialize report data -->
    <script>
        // Initialize report data with yesterday's date
        window.reportData = <?php
                            $yesterday = date('Y-m-d', strtotime('-1 day'));
                            $reportController = new ReportController();
                            $data = $reportController->generateReport($yesterday, $yesterday);
                            echo json_encode([
                                'status' => 'success',
                                'data' => $data
                            ]);
                            ?>;

        // Wait for all scripts to load
        window.addEventListener('load', function() {
            if (window.reportData && window.reportData.data) {
                window.reportManager = new ReportManager();
            } else {
                console.error('Failed to load initial report data');
            }
        });
    </script>

    <!-- Custom report script -->
    <script src="/src/script/report.js"></script>
</body>

</html>