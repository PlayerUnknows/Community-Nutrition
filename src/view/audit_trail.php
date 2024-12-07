<?php
require_once '../backend/audit_trail.php';
session_start();

/* Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
*/
// Get filter parameters
$filters = [];
if (isset($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
if (isset($_GET['action'])) $filters['action'] = $_GET['action'];
if (isset($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
if (isset($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

$auditTrails = getAuditTrails($filters);

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
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Action Type</label>
                        <select name="action" class="form-select">
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
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Filter</button>
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

    <script src="../../assets/dist/bootstrap.min.js"></script>
    <script src="../../assets/dist/datatable.js"></script>
    <script>
        $(document).ready(function() {
            $('#auditTable').DataTable({
                order: [[0, 'desc']]
            });
        });
    </script>
</body>
</html>
