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
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Contact</th>
                                    <th>Family Record</th>
                                    <th>Medical History</th>
                                    <th>Restrictions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <script>
                $(document).ready(function() {
                    const patientTable = new DataTable('#patientTable', {
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '../backend/fetch_patients.php',
                            method: 'POST'
                        },
                        columns: [
                            { data: 'id' },
                            { data: 'name' },
                            { data: 'age' },
                            { data: 'contact' },
                            { data: 'family_record' },
                            { data: 'medical_history' },
                            { data: 'restrictions' },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    return `
                                        <button class="btn btn-sm btn-primary edit-patient" data-id="${row.id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-patient" data-id="${row.id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    `;
                                }
                            }
                        ],
                        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                             '<"row"<"col-sm-12"tr>>' +
                             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                        pageLength: 10,
                        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
                    });
                });
                </script>
            </div>