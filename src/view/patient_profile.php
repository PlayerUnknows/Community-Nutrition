<?php
include '../models/patient_model.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-responsive {
            max-height: none; /* Remove the height restriction to eliminate scrolling */
            overflow-y: visible; /* Ensure that overflow is visible */
        }
        #patientTable {
            width: 100%;
            table-layout: auto; /* Make the table fit the data */
        }
        th, td {
            padding: 0.5rem; /* Reduce padding for table cells */
            font-size: 0.9rem; /* Make text slightly smaller */
        }
    </style>
</head>
<body>
<div class="tab-content" id="myTabContent">
    <!-- Patient Profile Section -->
    <div class="tab-pane fade show active" id="patients" role="tabpanel" aria-labelledby="patients-tab">
        <div class="container mt-4">
            <h2>Patient Profile</h2>
            <div class="table-responsive">
                <table id="patientTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Family ID</th>
                            <th>Patient ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Age</th>
                            <th>Other Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Assuming a function getPatients() fetches patient data from the database
                        $patients = getPatients();
                        foreach ($patients as $patient) {
                            echo "<tr>";
                            echo "<td>{$patient['patient_fam_id']}</td>";
                            echo "<td>{$patient['patient_id']}</td>";
                            echo "<td>{$patient['patient_fname']}</td>";
                            echo "<td>{$patient['patient_lname']}</td>";
                            echo "<td>{$patient['age']}</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-info view-patient' data-id='{$patient['patient_id']}' data-details='" . json_encode($patient) . "'>";
                            echo "<i class='fas fa-eye'></i> View";
                            echo "</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Scripts -->
        <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
        <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
        <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
        <!-- Custom Script -->
        <script src="../../src/script/patient_profile.js"></script>
    </div>
</div>
</body>
</html>