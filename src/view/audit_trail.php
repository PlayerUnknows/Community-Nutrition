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
    <link rel="stylesheet" href="../../assets/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/dist/datatable.css">
</head>

<body>
    <div class="container-fluid mt-4">
        <h2>System Audit Trail</h2>
        
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
                            <option value="UPDATED_USER">Update</option>
                            <option value="DELETED_USER">Delete</option>
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
        <div class="table-responsive">
            <table id="auditTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditTrails as $audit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($audit['action_timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($audit['username']); ?></td>
                        <td><?php echo htmlspecialchars($audit['action']); ?></td>
                        <td><?php echo htmlspecialchars($audit['ip_address']); ?></td>
                        <td>
                            <?php 
                            $details = $audit['details'];
                            if ($details) {
                                $decodedDetails = json_decode($details, true);
                                if ($decodedDetails) {
                                    $logger->info('mama mo!');
                                    echo '<pre class="mb-0" style="max-height: 100px; overflow-y: auto;">';
                                    echo htmlspecialchars(json_encode($decodedDetails, JSON_PRETTY_PRINT));
                                    echo '</pre>';
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

    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#auditTable').DataTable({
                order: [[0, 'desc']]
            });
        });
    </script>
    <script src="../script/audit_trail.js"></script>
</body>

</html>