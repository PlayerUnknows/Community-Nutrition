<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arm Circumference Report</title>
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
            width: 100%;
            margin-bottom: 20px;
        }

        .card-body {
            min-height: 450px;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
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
        }

        .bg-blue {
            background-color: #4169E1 !important;
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

        .input-group {
            max-width: 400px;
        }

        /* Toast styles */
        .toast {
            opacity: 1 !important;
        }

        #toastContainer {
            z-index: 1070;
        }

        .toast-body {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Search bar styles */
        .arm-clear-search {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            z-index: 4;
            border: none;
            background: transparent;
            padding: 0 12px;
        }

        .arm-clear-search:hover {
            color: #dc3545;
            background-color: rgba(0, 0, 0, 0.05);
        }

        #armTableSearch {
            padding-right: 40px;
            border-radius: 0.25rem;
        }

        .arm-table-controls .input-group {
            position: relative;
        }

        /* Table control styles */
        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_info,
        .dataTables_paginate {
            margin-top: 1rem;
        }

        .dataTables_paginate {
            display: flex;
            justify-content: flex-end;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 767px) {
            .col-md-6 {
                margin-bottom: 1rem;
            }

            .input-group {
                width: 100%;
                max-width: none;
            }

            .dataTables_paginate {
                justify-content: center;
            }

            .dataTables_info {
                text-align: center;
            }
        }

        /* Search control styles */
        .search-container {
            position: relative;
        }

        #armTableSearch {
            padding-left: 2.5rem;
            padding-right: 2.5rem;
        }

        .arm-clear-search {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
        }

        .arm-clear-search:hover {
            color: #dc3545;
        }

        /* Table control spacing */
        .dataTables_info,
        .dataTables_paginate {
            margin-top: 1rem;
        }

        /* Responsive styles */
        @media (max-width: 767.98px) {
            .d-flex.align-items-center {
                justify-content: center;
            }

            .search-container {
                margin-top: 1rem;
            }
        }

        .input-group-text {
            background-color: #fff;
            border-left: none;
        }

        #armTableSearch {
            border-right: none;
        }

        #armTableSearch:focus+.input-group-text {
            border-color: #86b7fe;
        }

        .fa-search {
            color: #6c757d;
        }
    </style>
    <!-- Add these in the head section -->
    <link rel="stylesheet" type="text/css" href="../../node_modules/daterangepicker/daterangepicker.css" />
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12 mb-4">
                <h2><i class="fas fa-ruler"></i> Arm Circumference Analytics Report</h2>
            </div>
        </div>

        <!-- Export buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-eye"></i> Preview Report
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item arm-preview-link" href="#" data-type="distribution">View Overall Distribution</a></li>
                        <li><a class="dropdown-item arm-preview-link" href="#" data-type="female">View Female Distribution</a></li>
                        <li><a class="dropdown-item arm-preview-link" href="#" data-type="male">View Male Distribution</a></li>
                        <li><a class="dropdown-item arm-preview-link" href="#" data-type="table">View Table Data</a></li>
                    </ul>
                </div>
                <div class="btn-group ms-2">
                    <button type="button" class="btn btn-success" id="exportTableBtn" data-export="excel" data-type="table">
                        <i class="fas fa-file-excel me-1"></i> Export Arm Circumference History
                    </button>
                </div>
            </div>
        </div>

        <!-- Replace your existing date filter section with this -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-ruler"></i> Arm Circumference Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="armDateRange" class="form-control" placeholder="Select date range">
                                <button class="btn btn-primary" id="applyArmDateRange">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="armCircumferenceBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gender-specific Charts -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-pink text-white">
                        <h5 class="mb-0"><i class="fas fa-venus"></i> Female Distribution</h5>
                    </div>
                    <div class="card-body">
                        <!-- <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="armFemaleDateRange" class="form-control" placeholder="Select date range">
                                <button class="btn btn-pink" id="applyArmFemaleDateRange">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                            </div>
                        </div> -->
                        <div class="chart-container">
                            <canvas id="femaleCircumferenceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-blue text-white">
                        <h5 class="mb-0"><i class="fas fa-mars"></i> Male Distribution</h5>
                    </div>
                    <div class="card-body">
                        <!-- <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="armMaleDateRange" class="form-control" placeholder="Select date range">
                                <button class="btn btn-blue" id="applyArmMaleDateRange">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                            </div>
                        </div> -->
                        <div class="chart-container">
                            <canvas id="maleCircumferenceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Measurements Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Arm Circumference History</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and Entries Controls -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <!-- Show entries on the left -->
                                    <div class="d-flex align-items-center">
                                        <label class="me-2">Show</label>
                                        <select id="armTableEntriesSelect" class="form-select form-select-sm" style="width: 70px;">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="-1">All</option>
                                        </select>
                                        <label class="ms-2">entries</label>
                                    </div>

                                    <!-- Search bar on the right with only search icon -->
                                    <div class="input-group input-group-sm" style="width: 200px;">
                                        <input type="text" id="armTableSearch" class="form-control form-control-sm" placeholder="Search records...">
                                        <span class="input-group-text border-start-0">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table id="armCircumferenceTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Arm Circumference (cm)</th>
                                        <th>Status</th>
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
    </div>

    <!-- Scripts -->
    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="../../node_modules/moment/moment.js"></script>
    <!-- Add these before your arm_circumference_report.js script -->
    <script src="../../node_modules/daterangepicker/daterangepicker.js"></script>
    <script src="../../node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>

    <!-- Custom arm circumference report script -->
    <script src="/src/script/arm_circumference_report.js"></script>
</body>

</html>