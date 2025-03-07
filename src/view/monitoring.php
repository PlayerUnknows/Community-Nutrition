<?php
// Include header
require_once '../includes/header.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="../../node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css">
<style>
    .table-responsive {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    /* Remove any default widths and make columns responsive */
    #monitoringTable th,
    #monitoringTable td {
        width: auto !important;
        /* Override any default widths */
        white-space: nowrap;
        /* Prevent wrapping of text */
    }


    /* Button styles */
    .btn-view {
        padding: 4px 8px;
        font-size: 0.775rem;
    }

    /* Container styles */
    .card-body {
        padding: 0;
    }
</style>

<!-- Main Content -->
<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
                <!-- Show entries dropdown -->
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <label class="me-2 mb-0">Show</label>
                    <select class="form-select form-select-sm w-auto" id="monitoringPerPage">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="-1">All</option>
                    </select>
                    <label class="ms-2 mb-0">entries</label>
                </div>

                <!-- Search input group -->
                <div class="input-group mb-2 mb-md-0 w-25">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="search"
                        class="form-control form-control-sm"
                        id="monitoringSearch"
                        placeholder="Search..."
                        autocomplete="off"
                        spellcheck="false">
                </div>

                <!-- Action buttons -->
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-success me-2" id="downloadTemplateBtn">
                        <i class="fas fa-file-download"></i> Download Template
                    </button>
                    <button type="button" class="btn btn-primary me-2" id="exportMonitoringBtn">
                        <i class="fas fa-file-export"></i> Export Data
                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importDataModal">
                        <i class="fas fa-file-upload"></i> Import Data
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <i class="fas fa-chart-line me-1"></i>
                    Nutrition Monitoring Records
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <!-- Table container -->
                <div class="table-responsive">
                    <table id="monitoringTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Patient ID</th>
                                <th>Family ID</th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>BMI Status</th>
                                <th>Growth Status</th>
                                <th>Arm Circumference<br>Status</th>
                                <!-- <th>Temperature</th> -->
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
</div>


<!-- Details Modal -->
<div class="modal fade" id="monitoringDetailsModal" tabindex="-1" aria-labelledby="monitoringDetailsModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="monitoringDetailsModalLabel">Additional Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- Assessment Results -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Assessment Results</h6>
                        <div class="mb-2">
                            <strong>Height:</strong> <span id="growthStatus"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Weight:</strong> <span id="weight"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Blood Pressure:</strong> <span id="bp"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Temperature:</strong> <span id="temperature"></span>
                        </div>
                    </div>
                    <!-- Appointment Information -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Appointment Information</h6>
                        <div class="mb-2">
                            <strong>Findings:</strong> <span id="findings"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Appointment Date:</strong> <span id="appointmentDate"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Appointment Time:</strong> <span id="appointmentTime"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Place:</strong> <span id="place"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Created At:</strong> <span id="createdAt"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" id="viewHistoryBtn">
                    <i class="fas fa-history"></i> View History
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update the history modal -->
<div class="modal fade" id="checkupHistoryModal" tabindex="-1" aria-labelledby="checkupHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="checkupHistoryModalLabel">Patient Checkup History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- No history message -->
                <div id="noHistoryMessage" class="text-center py-5 d-none">
                    <i class="fas fa-history fa-3x text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">No checkup history available</h5>
                </div>

                <!-- History table -->
                <div id="historyTableContainer" class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Weight Category</th>
                                <th>BMI Status</th>
                                <th>Growth Status</th>
                                <th>Arm Circumference</th>
                                <th>Findings</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importDataModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="importModalLabel">Import Monitoring Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Choose CSV File</label>
                        <input type="file" class="form-control" id="importFile" name="importFile" accept=".csv" required>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i> Please make sure your CSV file matches the template format.
                            You can download the template using the "Download Template" button.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmImportBtn">Import</button>
            </div>
        </div>
    </div>
</div>



<!-- Scripts -->

<script src="../script/monitoring.js"></script>