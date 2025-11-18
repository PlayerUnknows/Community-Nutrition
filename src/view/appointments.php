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
                            <th style="min-width: 160px;">Actions</th>
                            <th>Status</th>
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
<script src="/src/script/guardians.js"></script>
<link rel="stylesheet" href="../../assets/css/appointment.css"">

<!-- Add Appointment Modal -->
<div class="modal" 
     id="addAppointmentModal" 
     tabindex="-1" 
     role="dialog"
     data-bs-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule New Appointment</h5>
                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <form id="appointmentForm">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User ID</label>
                        <input type="text" class="form-control" id="user_id" name="user_id" required>
                        <div class="invalid-feedback">User ID is required</div>
                    </div>
                    <div id="guardian_container" class="mb-3" style="display: none;">
                        <!-- Guardian selector will be dynamically added here -->
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Patient Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                        <div class="invalid-feedback">Patient name is required</div>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                        <div class="invalid-feedback">Please select a valid appointment date</div>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="time" name="time" required>
                        <div class="invalid-feedback">Appointment time is required</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        <div class="invalid-feedback">Please provide a description for this appointment</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" 
                        class="btn btn-secondary" 
                        data-bs-dismiss="modal">Close</button>
                <button type="button" 
                        class="btn btn-primary" 
                        id="saveAppointment">Save Appointment</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Appointment Modal -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAppointmentModalLabel">Edit Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAppointmentForm">
                    <input type="hidden" id="edit_appointment_id" name="appointment_prikey">
                    <div class="mb-3">
                        <label for="edit_user_id" class="form-label">User ID</label>
                        <input type="text" class="form-control" id="edit_user_id" name="user_id" readonly>
                    </div>
                    <div class="mb-3"> 
                        <label for="edit_patient_name" class="form-label">Patient Name</label>
                        <input type="text" class="form-control" id="edit_patient_name" name="full_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_date" name="date" required>
                        <div class="invalid-feedback">Please select a valid appointment date</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="edit_time" name="time" required>
                        <div class="invalid-feedback">Appointment time is required</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        <div class="invalid-feedback">Please provide a description for this appointment</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateAppointment">Update Appointment</button>
            </div>
        </div>
    </div>
</div>
