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
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <style>
        .audit-details {
            font-size: 0.9rem;
            line-height: 1.4;
            max-height: 120px;
            overflow-y: auto;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .audit-details div {
            margin-bottom: 4px;
        }

        .audit-details strong {
            color: #495057;
        }
    </style>
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
                    <?php
                    $details = $audit['details'];
                    if ($details) {
                        $decodedDetails = json_decode($details, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Handle UPDATE_USER action more comprehensively
                            if ($audit['action'] === 'UPDATE_USER') {
                                echo '<div class="audit-details">';

                                // Display User ID being updated
                                if (isset($decodedDetails['updated_user_id'])) {
                                    echo "<div><strong>User ID:</strong> {$decodedDetails['updated_user_id']}</div>";
                                }

                                // Prepare role mapping
                                $roleMap = [
                                    '1' => 'Admin',
                                    '2' => 'Staff',
                                    '3' => 'User'
                                ];

                                // Display changes
                                $changes = [];

                                // Check email change
                                if (
                                    isset($decodedDetails['old_email']) && isset($decodedDetails['updated_user_email'])
                                    && $decodedDetails['old_email'] !== $decodedDetails['updated_user_email']
                                ) {
                                    $changes[] = "<strong>Email:</strong> {$decodedDetails['old_email']} → {$decodedDetails['updated_user_email']}";
                                }

                                // Check role change
                                if (
                                    isset($decodedDetails['old_role']) && isset($decodedDetails['new_role'])
                                    && $decodedDetails['old_role'] !== $decodedDetails['new_role']
                                ) {
                                    $oldRole = $roleMap[$decodedDetails['old_role']] ?? 'Unknown';
                                    $newRole = $roleMap[$decodedDetails['new_role']] ?? 'Unknown';
                                    $changes[] = "<strong>Role:</strong> {$oldRole} → {$newRole}";
                                }

                                // Display changes if any
                                if (!empty($changes)) {
                                    echo implode('<br>', $changes);
                                } else {
                                    echo "No significant changes";
                                }

                                echo '</div>';
                            }
                            // Handle other actions as before
                            else if ($audit['action'] === 'LOGIN' || $audit['action'] === 'LOGOUT') {
                                echo "<div class='audit-details'>";
                                echo "<div>" . ($audit['action'] === 'LOGIN' ? 'User logged in' : 'User logged out') . "</div>";
                                echo "</div>";
                            } else {
                                echo '<div class="audit-details">';
                                foreach ($decodedDetails as $key => $value) {
                                    $label = ucwords(str_replace('_', ' ', $key));
                                    echo "<div><strong>{$label}:</strong> {$value}</div>";
                                }
                                echo '</div>';
                            }
                        } else {
                            echo htmlspecialchars($details);
                        }
                    } else {
                        echo "-";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#auditTable').DataTable({
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
</body>

</html>