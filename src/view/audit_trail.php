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
            padding: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 1rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: calc(100vh - 280px);
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
            margin-bottom: 1rem;
        }

        .filter-card .card-body {
            padding: 1rem;
        }

        /* Header styling */
        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 500;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .audit-container {
                width: 100%;
                padding: 15px;
            }

            .table-wrapper {
                margin-top: 0.5rem;
                height: calc(100vh - 230px);
            }

            .filter-card .card-body {
                padding: 0.75rem;
            }

            #auditTable {
                font-size: 0.8125rem;
            }

            #auditTable td,
            #auditTable th {
                padding: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="audit-container">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1 class="text-primary mb-0">System Audit Trail</h1>
        </div>

        <!-- Filter Form -->
        <div class="filter-card">
            <div class="card-body">
                <form method="GET" class="row g-3 audit-filter-form">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Action Type</label>
                        <select name="action" class="form-select form-select-sm">
                            <option value="">All Actions</option>
                            <option value="LOGIN">Login</option>
                            <option value="LOGOUT">Logout</option>
                            <option value="REGISTER">Register</option>
                            <option value="CREATE">Create</option>
                            <option value="UPDATE">Update</option>
                            <option value="DELETE">Delete</option>
                            <option value="VIEW">View</option>
                            <option value="SYSTEM_CHANGE">System Change</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-sm d-block w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Audit Trail Table -->
        <div class="table-wrapper">
            <div class="table-scroll">
                <table id="auditTable" class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditTrails as $audit): ?>
                            <tr>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($audit['action_timestamp']);
                                    if (isset($audit['count']) && $audit['count'] > 1) {
                                        echo '<br><small class="text-muted">(' . $audit['count'] . ' similar actions)</small>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($audit['username'] ?? 'System'); ?></td>
                                <td><?php echo htmlspecialchars($audit['action']); ?></td>
                                <td>
                                    <?php
                                    $details = $audit['details'];
                                    if ($details) {
                                        $decodedDetails = json_decode($details, true);
                                        if (json_last_error() === JSON_ERROR_NONE) {
                                            echo '<div class="audit-details">';
                                            
                                            // Handle UPDATE_USER action
                                            if ($audit['action'] === 'UPDATE_USER') {
                                                // Display User ID being updated
                                                if (isset($decodedDetails['updated_user_id'])) {
                                                    echo "<div><strong>User ID:</strong> {$decodedDetails['updated_user_id']}</div>";
                                                }

                                                // Role mapping
                                                $roleMap = [
                                                    '1' => 'Admin',
                                                    '2' => 'Staff',
                                                    '3' => 'User'
                                                ];

                                                // Display unique changes
                                                $changes = [];
                                                
                                                // Email changes
                                                if (isset($decodedDetails['old_email'], $decodedDetails['updated_user_email'])) {
                                                    $oldEmails = (array)$decodedDetails['old_email'];
                                                    $newEmails = (array)$decodedDetails['updated_user_email'];
                                                    $uniqueChanges = array_unique(array_map(function($old, $new) {
                                                        return "$old → $new";
                                                    }, $oldEmails, $newEmails));
                                                    
                                                    foreach ($uniqueChanges as $change) {
                                                        $changes[] = "<strong>Email:</strong> $change";
                                                    }
                                                }

                                                // Role changes
                                                if (isset($decodedDetails['old_role'], $decodedDetails['new_role'])) {
                                                    $oldRoles = (array)$decodedDetails['old_role'];
                                                    $newRoles = (array)$decodedDetails['new_role'];
                                                    $uniqueChanges = array_unique(array_map(function($old, $new) use ($roleMap) {
                                                        $oldRole = $roleMap[$old] ?? 'Unknown';
                                                        $newRole = $roleMap[$new] ?? 'Unknown';
                                                        return "$oldRole → $newRole";
                                                    }, $oldRoles, $newRoles));
                                                    
                                                    foreach ($uniqueChanges as $change) {
                                                        $changes[] = "<strong>Role:</strong> $change";
                                                    }
                                                }

                                                if (!empty($changes)) {
                                                    echo implode('<br>', array_unique($changes));
                                                } else {
                                                    echo "No significant changes";
                                                }
                                            } else {
                                                // For other actions, display unique details
                                                $displayedDetails = [];
                                                foreach ($decodedDetails as $key => $value) {
                                                    if (is_array($value)) {
                                                        $value = array_unique((array)$value);
                                                        $value = implode(', ', $value);
                                                    }
                                                    $displayKey = ucwords(str_replace('_', ' ', $key));
                                                    if (!isset($displayedDetails[$displayKey])) {
                                                        echo "<div><strong>" . htmlspecialchars($displayKey) . ":</strong> " . htmlspecialchars($value) . "</div>";
                                                        $displayedDetails[$displayKey] = true;
                                                    }
                                                }
                                            }
                                            echo '</div>';
                                        } else {
                                            echo htmlspecialchars($details);
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Wait for DataTables to be available
        function waitForDataTables(callback) {
            if (typeof $.fn.DataTable !== 'undefined') {
                callback();
            } else {
                setTimeout(function() {
                    waitForDataTables(callback);
                }, 100);
            }
        }

        // Initialize table only when DataTables is available
        waitForDataTables(function() {
            if (typeof window.initializeAuditTable === 'function') {
                window.initializeAuditTable();
            } else {
                var table = $('#auditTable');
                if (table.length) {
                    table.DataTable({
                        order: [[0, 'desc']],
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                        language: {
                            lengthMenu: "Show _MENU_ entries",
                            search: "Search:",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries (Limited to last 100 records)",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        },
                        drawCallback: function() {
                            $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                        }
                    });
                }
            }
        });
    </script>
    <script src="../script/audit_trail.js"></script>
</body>

</html>