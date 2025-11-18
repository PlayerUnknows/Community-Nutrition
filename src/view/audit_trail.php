<?php
require_once '../backend/audit_trail.php';
session_start();

// Set default limit for records
$limit = 100; // Default limit of records to fetch

// Get filter parameters
$filters = [
    'limit' => $limit // Add limit to filters
];
if (isset($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
if (isset($_GET['action'])) $filters['action'] = $_GET['action'];
if (isset($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
if (isset($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

$auditTrails = getAuditTrails($filters);

// Function to generate a unique key for each audit entry
function getAuditKey($audit) {
    return $audit['username'] . '_' . $audit['action'] . '_' . date('Y-m-d H:i', strtotime($audit['action_timestamp']));
}

// Merge duplicate entries
$mergedAuditTrails = [];
foreach ($auditTrails as $audit) {
    $key = getAuditKey($audit);
    
    if (!isset($mergedAuditTrails[$key])) {
        $mergedAuditTrails[$key] = $audit;
        $mergedAuditTrails[$key]['count'] = 1;
    } else {
        // Increment count for duplicate actions
        $mergedAuditTrails[$key]['count']++;
        
        // Keep the latest timestamp
        if (strtotime($audit['action_timestamp']) > strtotime($mergedAuditTrails[$key]['action_timestamp'])) {
            $mergedAuditTrails[$key]['action_timestamp'] = $audit['action_timestamp'];
        }
        
        // Merge details if they're different
        if ($audit['details'] !== $mergedAuditTrails[$key]['details']) {
            $currentDetails = json_decode($mergedAuditTrails[$key]['details'], true) ?? [];
            $newDetails = json_decode($audit['details'], true) ?? [];
            
            if (is_array($currentDetails) && is_array($newDetails)) {
                $mergedDetails = array_merge_recursive($currentDetails, $newDetails);
                $mergedAuditTrails[$key]['details'] = json_encode($mergedDetails);
            }
        }
    }
}

// Convert back to indexed array and sort by timestamp
$auditTrails = array_values($mergedAuditTrails);
usort($auditTrails, function($a, $b) {
    return strtotime($b['action_timestamp']) - strtotime($a['action_timestamp']);
});

// Debug output
error_log("Merged Audit Trails Count: " . count($auditTrails));
if (!empty($auditTrails)) {
    error_log("First Merged Record: " . print_r($auditTrails[0], true));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - Community Nutrition System</title>
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <style>
        .audit-details {
            font-size: 0.875rem;
            line-height: 1.4;
            max-height: 100px;
            overflow-y: auto;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin: 0;
        }

        .audit-details div {
            margin-bottom: 4px;
        }

        .audit-details strong {
            color: #495057;
        }

        /* DataTables Bootstrap 5 Styling */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length select {
            width: auto;
            display: inline-block;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .pagination {
            justify-content: flex-end;
        }

        .page-link {
            padding: 0.375rem 0.75rem;
        }

        /* Layout styles */
        .audit-container {
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 0.5rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: calc(100vh - 200px);
            position: relative;
        }

        .table-scroll {
            overflow-y: auto;
            height: 100%;
            border-radius: 8px;
        }

        #auditTable {
            margin: 0;
            width: 100%;
            font-size: 0.875rem;
        }

        #auditTable thead th {
            position: sticky;
            top: 0;
            background-color: #0d6efd;
            color: white;
            z-index: 1;
            padding: 0.75rem;
            font-weight: 500;
        }

        #auditTable tbody tr:first-child td {
            border-top: none;
        }

        #auditTable td {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            background-color: white;
            padding: 0.75rem;
            vertical-align: middle;
        }

        #auditTable td:last-child {
            max-width: none;
        }

        .filter-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 0.5rem;
        }

        .filter-card .card-body {
            padding: 0.5rem;
        }

        .page-header {
            margin-bottom: 0.5rem;
        }

        /* Header styling */
        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 500;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .audit-container {
                width: 100%;
                padding: 10px;
            }

            .table-wrapper {
                margin-top: 0.5rem;
                height: calc(100vh - 200px);
            }

            .filter-card .card-body {
                padding: 0.5rem;
            }

            #auditTable {
                font-size: 0.8125rem;
            }

            #auditTable td,
            #auditTable th {
                padding: 0.5rem;
            }
        }

        /* Update/add these responsive styles */
        .table-responsive {
            margin: 0;
            padding: 0;
            border: none;
        }

        #auditTable {
            width: 100% !important;
            margin: 0;
        }

        @media screen and (max-width: 768px) {
            /* Adjust filter form on mobile */
            .audit-filter-form .col-md-3 {
                margin-bottom: 10px;
            }
            
            /* Make table cells more readable on mobile */
            #auditTable td {
                min-width: 120px; /* Ensure minimum width for readability */
            }
            
            #auditTable td:nth-child(3) { /* Details column */
                min-width: 200px;
            }
            
            .audit-details {
                max-height: 150px; /* Increase height for mobile viewing */
                font-size: 0.8rem;
            }
            
            /* Adjust filter elements spacing */
            .form-label {
                margin-bottom: 0.25rem;
            }
            
            /* Optimize table header */
            #auditTable thead th {
                white-space: nowrap;
                padding: 0.5rem;
            }
        }

        /* Add horizontal scroll indicator */
        .table-responsive::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 50px;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.1));
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .table-responsive.has-scroll::after {
            opacity: 1;
        }

        /* Update the table container and positioning styles */
        .card-body {
            padding: 1rem;
            position: relative;
        }

        /* Fix table container positioning */
        .table-responsive {
            position: relative;
            width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            overflow-x: auto;
        }

        /* DataTable wrapper adjustments */
        .dataTables_wrapper {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Fix table layout */
        #auditTable {
            width: 100% !important;
            margin: 0 !important;
            border-collapse: collapse;
        }

        /* Adjust column widths */
        #auditTable th,
        #auditTable td {
            padding: 0.75rem;
        }

        #auditTable th:nth-child(1), /* Username */
        #auditTable td:nth-child(1) {
            width: 20%;
        }

        #auditTable th:nth-child(2), /* Action */
        #auditTable td:nth-child(2) {
            width: 15%;
        }

        #auditTable th:nth-child(3), /* Details */
        #auditTable td:nth-child(3) {
            width: 45%;
        }

        #auditTable th:nth-child(4), /* Timestamp */
        #auditTable td:nth-child(4) {
            width: 20%;
        }

        /* Fix DataTables controls positioning */
        .dataTables_length,
        .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_info,
        .dataTables_paginate {
            margin-top: 1rem;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .card-body {
                padding: 0.5rem;
            }
            
            #auditTable th,
            #auditTable td {
                padding: 0.5rem;
            }
            
            .dataTables_wrapper .row {
                margin: 0;
            }
            
            .dataTables_length,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate {
                padding: 0.5rem;
            }
        }

        /* Update backdrop styles */
        #loadingBackdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        #loadingBackdrop.show {
            opacity: 1;
        }

        #loadingSpinner {
            z-index: 1051;
            pointer-events: none;
        }

        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important;
        }

        /* Ensure no stacking of backdrops */
        .modal-backdrop + .modal-backdrop {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">System Audit Trail</h5>
                <button id="showFilterBtn" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Show Filters
                </button>
            </div>
            <div class="card-body">
                <!-- Filter Form - Initially hidden -->
                <div id="filterSection" class="row mb-3" style="display: none;">
                    <div class="col-md-12">
                        <form id="auditFilterForm" class="audit-filter-form row g-3" method="GET">
                            <div class="col-md-3">
                                <label class="form-label">Action Type</label>
                                <select name="action" class="form-select">
                                    <option value="">All Actions</option>
                                    <option value="LOGIN">Login</option>
                                    <option value="LOGOUT">Logout</option>
                                    <option value="REGISTER">Register</option>
                                    <option value="UPDATED_USER">Update</option>
                                    <option value="DELETED_USER">Delete</option>
                                    <option value="VIEW">View</option>
                                    <option value="SYSTEM_CHANGE">System Change</option>
                                    <option value="FILE_DOWNLOAD">File Download</option>
                                    <option value="FILE_EXPORT">File Export</option>
                                    <option value="FILE_IMPORT">File Import</option>
                                    <option value="EVENT_CREATE">Event Create</option>
                                    <option value="EVENT_UPDATE">Event Update</option>
                                    <option value="EVENT_DELETE">Event Delete</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">Apply Filters</button>
                                    <button type="button" id="clearFiltersBtn" class="btn btn-secondary">Clear</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="auditTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($auditTrails as $audit): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($audit['username'] ?? 'System'); ?></td>
                                    <td><?php echo htmlspecialchars($audit['action']); ?></td>
                                    <td>
                                        <?php
                                        $details = $audit['details'];
                                        if ($details) {
                                            $decodedDetails = json_decode($details, true);
                                            if (json_last_error() === JSON_ERROR_NONE) {
                                                echo '<div class="audit-details">';
                                                // Display all details generically
                                                    foreach ($decodedDetails as $key => $value) {
                                                        if (is_array($value)) {
                                                            $value = implode(', ', array_unique((array)$value));
                                                        }
                                                        $displayKey = ucwords(str_replace('_', ' ', $key));
                                                        echo "<div><strong>" . htmlspecialchars($displayKey) . ":</strong> " . 
                                                             htmlspecialchars((string)$value) . "</div>";
                                                    }
                                                echo '</div>';
                                            } else {
                                                echo htmlspecialchars($details);
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($audit['action_timestamp']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update the modal structure -->
    <div class="modal fade" 
         id="importDataModal" 
         tabindex="-1" 
         role="dialog"
         aria-labelledby="importModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                    <button type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal" 
                            aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form id="importDataForm">
                        <!-- Your form content -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal">Close</button>
                    <button type="submit" 
                            form="importDataForm" 
                            class="btn btn-primary">Import</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/moment/moment.js"></script>
    <script src="../script/audit_trail.js"></script>
</body>

</html>