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
                            <tbody>
                                <tr>
                                    <td>P001</td>
                                    <td>Juan Dela Cruz</td>
                                    <td>5</td>
                                    <td>09123456789</td>
                                    <td>F001</td>
                                    <td>Asthma</td>
                                    <td>Dairy</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-patient" data-id="P001">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-patient" data-id="P001">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>P002</td>
                                    <td>Maria Santos</td>
                                    <td>3</td>
                                    <td>09187654321</td>
                                    <td>F002</td>
                                    <td>None</td>
                                    <td>Nuts</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-patient" data-id="P002">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-patient" data-id="P002">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>P003</td>
                                    <td>Pedro Reyes</td>
                                    <td>7</td>
                                    <td>09198765432</td>
                                    <td>F003</td>
                                    <td>Allergies</td>
                                    <td>Seafood</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-patient" data-id="P003">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-patient" data-id="P003">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>P004</td>
                                    <td>Ana Gonzales</td>
                                    <td>4</td>
                                    <td>09234567890</td>
                                    <td>F004</td>
                                    <td>None</td>
                                    <td>None</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-patient" data-id="P004">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-patient" data-id="P004">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>P005</td>
                                    <td>Jose Garcia</td>
                                    <td>6</td>
                                    <td>09345678901</td>
                                    <td>F005</td>
                                    <td>Diabetes</td>
                                    <td>Sugar</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-patient" data-id="P005">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-patient" data-id="P005">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                $(document).ready(function() {
                    const patientTable = new DataTable('#patientTable', {
                        pageLength: 10,
                        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                             '<"row"<"col-sm-12"tr>>' +
                             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
                    });

                    // Handle edit button click
                    $('#patientTable').on('click', '.edit-patient', function() {
                        const patientId = $(this).data('id');
                        // Add your edit logic here
                        alert('Edit patient: ' + patientId);
                    });

                    // Handle delete button click
                    $('#patientTable').on('click', '.delete-patient', function() {
                        const patientId = $(this).data('id');
                        // Add your delete logic here
                        alert('Delete patient: ' + patientId);
                    });
                });
                </script>
            </div>