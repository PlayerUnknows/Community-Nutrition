<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../models/patient_model.php';
    $patients = getPatients();
} catch (Exception $e) {
    error_log("Error loading patients: " . $e->getMessage());
    $patients = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
    
    <!-- CSS -->
    <link href="../../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        .table-responsive {
            margin: 0;
            padding: 0;
            width: 100%;
        }
        #patientTable {
            width: 100% !important;
            margin: 0 !important;
        }
        .dataTables_wrapper .dataTables_scroll {
            margin-bottom: 0.5rem;
        }
        .dataTables_wrapper .dataTables_scrollBody {
            border-bottom: 1px solid #dee2e6;
        }
        .dataTables_scrollHead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .dataTables_scrollBody::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .dataTables_scrollBody::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .dataTables_scrollBody::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        #patientTable td, #patientTable th {
            padding: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Hide DataTables' default search box */
        .dataTables_filter {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="search" 
                                   class="form-control" 
                                   id="patientSearch" 
                                   placeholder="Search patient..."
                                   autocomplete="off"
                                   spellcheck="false">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="patientPerPage">
                            <option value="5" selected>5 per page</option>
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <h5 class="mb-0">Patient Records</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="patientTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Family ID</th>
                                <th>Patient ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Age</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($patient['patient_fam_id']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['patient_fname']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['patient_lname']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['age']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-patient" 
                                                    data-id="<?php echo htmlspecialchars($patient['patient_id']); ?>"
                                                    data-details='<?php echo htmlspecialchars(json_encode($patient), ENT_QUOTES, 'UTF-8'); ?>'>
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Details Modal -->
    <div class="modal fade" id="patientDetailsModal" tabindex="-1" aria-labelledby="patientDetailsModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientDetailsModalLabel">Patient Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be dynamically loaded -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    <script src="../../node_modules/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../../node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
    <script src="../script/patient_profile.js"></script>

    <script>
    // // Debug script loading
    // $(document).ready(function() {
    //     console.log('Document ready');
    //     console.log('jQuery version:', $.fn.jquery);
    //     console.log('DataTables version:', $.fn.dataTable.version);
    //     console.log('Bootstrap version:', typeof bootstrap !== 'undefined' ? bootstrap.Alert.VERSION : 'not loaded');
        
    //     // Check if table exists and has data
    //     console.log('Table exists:', $('#patientTable').length > 0);
    //     console.log('Table rows:', $('#patientTable tbody tr').length);
        
    //     // Check if search input exists
    //     console.log('Search input exists:', $('#patientSearch').length > 0);
        
    //     // Check if per page select exists
    //     console.log('Per page select exists:', $('#patientPerPage').length > 0);
    // });
    // </script>
</body>
</html>