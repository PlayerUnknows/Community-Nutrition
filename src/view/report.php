<?php
require_once __DIR__ . '/../controllers/ReportController.php';

// Helper functions for status and calculations
function getStatusClass($status) {
    $status = strtolower($status);
    if (strpos($status, 'normal') !== false || strpos($status, 'healthy') !== false) return 'status-normal';
    if (strpos($status, 'under') !== false || strpos($status, 'low') !== false) return 'status-warning';
    return 'status-alert';
}

function calculateIdealBMI($age) {
    // Based on WHO-CGS and Philippine Pediatric Society standards
    if ($age < 5) {
        return ['min' => 13.0, 'max' => 17.0]; // Under 5 years
    } else if ($age < 10) {
        return ['min' => 13.5, 'max' => 18.0]; // 5-9 years
    } else {
        return ['min' => 14.0, 'max' => 19.0]; // 10-14 years
    }
}

function calculateIdealHeight($age, $sex) {
    // Based on Philippine pediatric reference values
    if ($sex == 'Male') {
        if ($age < 2) return ['min' => 45, 'max' => 87];      // 0-24 months
        if ($age < 5) return ['min' => 87, 'max' => 110];     // 2-5 years
        if ($age < 10) return ['min' => 110, 'max' => 135];   // 5-9 years
        return ['min' => 135, 'max' => 163];                  // 10-14 years
    } else {
        if ($age < 2) return ['min' => 44, 'max' => 85];      // 0-24 months
        if ($age < 5) return ['min' => 85, 'max' => 109];     // 2-5 years
        if ($age < 10) return ['min' => 109, 'max' => 133];   // 5-9 years
        return ['min' => 133, 'max' => 157];                  // 10-14 years
    }
}

function calculateIdealArmCircumference($age) {
    // Based on Philippine MUAC standards
    if ($age < 2) {
        return ['min' => 11.0, 'max' => 13.0];  // 0-24 months
    } else if ($age < 5) {
        return ['min' => 12.5, 'max' => 14.5];  // 2-5 years
    } else if ($age < 10) {
        return ['min' => 14.5, 'max' => 17.0];  // 5-9 years
    } else {
        return ['min' => 16.0, 'max' => 19.0];  // 10-14 years
    }
}

function getStatusBadge($bmi) {
    // Based on Philippine standards for malnutrition classification
    $status = '';
    $className = '';
    
    if ($bmi < 14.5) {
        $status = 'Severely Underweight';
        $className = 'status-alert';
    } else if ($bmi < 16.0) {
        $status = 'Underweight';
        $className = 'status-warning';
    } else if ($bmi > 19.0) {
        $status = 'Overweight';
        $className = 'status-warning';
    } else {
        $status = 'Normal';
        $className = 'status-normal';
    }
    return "<span class=\"status-badge {$className}\">{$status}</span>";
}

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
        .status-normal { background-color: #28a745; color: white; }
        .status-warning { background-color: #ffc107; color: black; }
        .status-alert { background-color: #dc3545; color: white; }
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
                <button id="exportPDF" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button id="exportExcel" class="btn btn-success ms-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <div class="d-inline-block ms-3">
                    <label for="startDate">From:</label>
                    <input type="date" id="startDate" class="form-control d-inline-block" style="width: auto;">
                    
                    <label for="endDate" class="ms-2">To:</label>
                    <input type="date" id="endDate" class="form-control d-inline-block" style="width: auto;">
                </div>
                <select id="dateRangeFilter" class="form-select d-inline-block ms-2" style="width: auto;">
                    <option value="all">All Time</option>
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
        </div>

        <!-- Growth Trends -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Growth Trends Over Time</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="growthTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
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
                                        <th>Avg Weight (kg)</th>
                                        <th>Total Patients</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData['growthStatsByGender'] as $stat): ?>
                                    <tr>
                                        <td><?php echo $stat['age_group']; ?></td>
                                        <td><?php echo $stat['gender']; ?></td>
                                        <td><?php echo $stat['avg_height']; ?></td>
                                        <td><?php echo $stat['avg_weight']; ?></td>
                                        <td><?php echo $stat['total_patients']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BMI and Arm Circumference -->
        <div class="row mb-4">
            <div class="col-md-6">
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
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-ruler"></i> Arm Circumference Trends</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="armCircumferenceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Tables -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Measurements History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="measurementsTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Weight (kg)</th>
                                        <th>Height (cm)</th>
                                        <th>BMI</th>
                                        <th>Arm Circumference (cm)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): 
                                        $heightInMeters = $record['height'] / 100;
                                        $bmi = round($record['weight'] / ($heightInMeters * $heightInMeters), 2);
                                    ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($record['created_at'])); ?></td>
                                        <td><?php echo $record['weight']; ?></td>
                                        <td><?php echo $record['height']; ?></td>
                                        <td><?php echo $bmi; ?></td>
                                        <td><?php echo $record['arm_circumference']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo getStatusClass($record['finding_bmi']); ?>">
                                                <?php echo $record['finding_bmi']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="../../node_modules/moment/moment.js"></script>

    <!-- Initialize report data -->
    <script>
        // Make sure data is available before initializing charts
        window.reportData = {
            dates: <?php echo json_encode($dates); ?>,
            weights: <?php echo json_encode($weights); ?>,
            heights: <?php echo json_encode($heights); ?>,
            bmis: <?php echo json_encode($bmis); ?>,
            armCircumferences: <?php echo json_encode($armCircumferences); ?>
        };

        // Initialize DataTable
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('#measurementsTable').DataTable({
                    pageLength: 10,
                    order: [[0, 'desc']]
                });
            } else {
                console.error('DataTables is not properly loaded');
            }
        });
    </script>

    <!-- Custom report script -->
    <script src="/src/script/report.js"></script>
</body>
</html>