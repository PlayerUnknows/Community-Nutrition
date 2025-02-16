<?php
// Include header
require_once '../includes/header.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="../../node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css">
<style>
    /* Table container */
    .table-wrapper {
        width: 100%;
       
    }
    
    /* Table styles */
    #monitoringTable {
        table-layout: fixed;
        width: 1020px !important; /* Total of all column widths */
    }
    
    /* Remove sticky header styles */
    .dataTables_scrollHead {
        position: static !important;
    }
    
    .dataTables_scrollBody {
        position: static !important;
    }
    
    /* Fixed column widths */
    #monitoringTable th:nth-child(1),
    #monitoringTable td:nth-child(1) { width: 150px; }
    
    #monitoringTable th:nth-child(2),
    #monitoringTable td:nth-child(2) { width: 150px; }
    
    #monitoringTable th:nth-child(3),
    #monitoringTable td:nth-child(3) { width: 60px; }
    
    #monitoringTable th:nth-child(4),
    #monitoringTable td:nth-child(4) { width: 60px; }
    
    #monitoringTable th:nth-child(5),
    #monitoringTable td:nth-child(5) { width: 100px; }
    
    #monitoringTable th:nth-child(6),
    #monitoringTable td:nth-child(6) { width: 100px; }
    
    #monitoringTable th:nth-child(7),
    #monitoringTable td:nth-child(7) { width: 100px; }
    
    #monitoringTable th:nth-child(8),
    #monitoringTable td:nth-child(8) { width: 100px; }
    
    #monitoringTable th:nth-child(9),
    #monitoringTable td:nth-child(9) { width: 100px; }
    
    /* Cell styles */
    #monitoringTable th,
    #monitoringTable td {
        padding: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    #monitoringTable thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
    }
    
    /* Column alignments */
    .dt-left { text-align: left; }
    .dt-center { text-align: center; }
    .dt-right { text-align: right; }
    
    /* ID columns */
    #monitoringTable td:nth-child(1),
    #monitoringTable td:nth-child(2) {
        font-family: monospace;
        font-size: 14px;
        letter-spacing: -0.5px;
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
            <div class="row align-items-center mb-3">
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
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
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="search" 
                               class="form-control" 
                               id="monitoringSearch" 
                               placeholder="Search monitoring records..."
                               autocomplete="off"
                               spellcheck="false">
                    </div>
                </div>
                
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success me-2" id="downloadTemplateBtn">
                            <i class="fas fa-file-download"></i> Download Template
                        </button>
                        <button type="button" class="btn btn-warning me-2" id="importDataBtn">
                            <i class="fas fa-file-upload"></i> Import Data
                        </button>
                        <button type="button" class="btn btn-primary" id="exportMonitoringBtn">
                            <i class="fas fa-file-export"></i> Export Data
                        </button>
                    </div>
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
            <div class="table-wrapper">
                <table id="monitoringTable" class="table table-striped">
                    <colgroup>
                        <col style="width: 150px">
                        <col style="width: 150px">
                        <col style="width: 60px">
                        <col style="width: 60px">
                        <col style="width: 100px">
                        <col style="width: 100px">
                        <col style="width: 100px">
                        <col style="width: 100px">
                        <col style="width: 100px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="dt-left">Patient ID</th>
                            <th class="dt-left">Family ID</th>
                            <th class="dt-center">Age</th>
                            <th class="dt-center">Sex</th>
                            <th class="dt-right">Weight (kg)</th>
                            <th class="dt-right">Height (cm)</th>
                            <th class="dt-center">BP</th>
                            <th class="dt-right">Temperature</th>
                            <th class="dt-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
                            <strong>Weight Category:</strong> <span id="weightCategory"></span>
                        </div>
                        <div class="mb-2">
                            <strong>BMI Status:</strong> <span id="bmiStatus"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Growth Status:</strong> <span id="growthStatus"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Arm Circumference:</strong> <span id="armCircumference"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Arm Status:</strong> <span id="armStatus"></span>
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
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Monitoring Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
<script src="../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="../../node_modules/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
<script src="../script/monitoring.js"></script>