<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Appointments</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                <i class="fas fa-plus"></i> New Appointment
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="search" 
                               class="form-control" 
                               id="appointmentSearch" 
                               placeholder="Search appointments..."
                               autocomplete="off"
                               spellcheck="false">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="appointmentsPerPage">
                        <option value="5" selected>5 per page</option>
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <p id="appointments-showing-entries" class="text-muted mb-0 text-end">Showing 0 entries</p>
                </div>
            </div>

            <div class="table-container mb-3">
                <table id="appointmentsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Description</th>
                            <th>Guardian</th>
                            <th style="min-width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated dynamically -->
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-12">
                    <nav aria-label="Appointments pagination">
                        <ul class="pagination justify-content-end mb-0" id="appointmentsPagination">
                            
                            <li class="page-item page-numbers">
                                <!-- Page numbers will be inserted here -->
                            </li>
                            
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add script reference to guardians.js -->

<!-- appointment.css -->
<link rel="stylesheet" href="../../assets/css/appointment.css">

<!-- Add Appointment Modal -->
<?php include 'AppointmentModules/add_appointment_modal.php'; ?>


<!-- Edit Appointment Modal -->
<?php include 'AppointmentModules/edit_appointment_modal.php'; ?>

<!-- utilities -->
<script src="../script/appointment/utils/check_patient_exists.js"></script>
<script src="../script/appointment/utils/fetch_guardians.js"></script>
<script src="../script/appointment/utils/show_and_hide_modal_for_add.js"></script>
<script src="../script/appointment/utils/validations_inputs.js"></script>
<script src="../script/appointment/utils/validations_form.js"></script>
<script src="../script/appointment/utils/reset_form.js"></script>


<!-- main scripts-->
  <script src="../script/appointment/fetch_appointment.js"></script>
  <script src="../script/appointment/add_appointment.js"></script>
  <script src="../script/appointment/edit_button_fetch_appointment.js"></script>
  <script src="../script/appointment/update_appointment.js"></script>
  <script src="../script/appointment/cancel_appointment.js"></script>
  <script src="../script/appointment/appointments.js"></script>

