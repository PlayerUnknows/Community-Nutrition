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
<script src="/src/script/guardians.js"></script>

<style>
    .table-container {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .table-container table {
        margin-bottom: 0;
    }
    .table-container thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 1;
        border-top: none;
    }
    .table-container thead tr {
        border-bottom: 2px solid #dee2e6;
    }
    /* Custom scrollbar */
    .table-container::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    /* Ensure table takes full width */
    #appointmentsTable {
        width: 100% !important;
    }
    /* Fix header appearance */
    #appointmentsTable th {
        white-space: nowrap;
        padding: 12px 15px;
    }
    #appointmentsTable td {
        vertical-align: middle;
    }
    .page-numbers {
        display: flex;
        margin: 0;
    }
    .page-numbers .page-link {
        margin: 0 2px;
    }
    .page-numbers .page-link.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
    .page-numbers span.page-link {
        background: none;
        border: none;
    }
    .modal-open {
        overflow: hidden;
        padding-right: 0 !important;
    }
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        opacity: 0.5;
    }
    .btn-state-loading {
        pointer-events: none;
        position: relative;
        padding-left: 2.5rem !important;
    }
    .btn-state-loading:before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1rem;
        height: 1rem;
        border: 2px solid #fff;
        border-radius: 50%;
        border-right-color: transparent;
        animation: spin 0.75s linear infinite;
    }
    @keyframes spin {
        to { transform: translateY(-50%) rotate(360deg); }
    }
    .btn .spinner-border-sm {
        margin-right: 5px;
        width: 1rem;
        height: 1rem;
    }
    .btn.btn-success,
    .btn.btn-danger {
        transition: all 0.3s ease;
    }
    .btn i {
        margin-right: 5px;
    }
    
    /* Patient validation message styles */
    #patient-validation-message {
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    #patient-validation-message .text-success {
        color: #198754 !important;
    }
    
    #patient-validation-message .text-danger {
        color: #dc3545 !important;
    }
    
    #patient-validation-message .text-warning {
        color: #ffc107 !important;
    }
    
    #patient-validation-message .text-info {
        color: #0dcaf0 !important;
    }
    
    #patient-validation-message i {
        margin-right: 0.5rem;
    }
    
    /* Guardian container styles */
    #guardian_container {
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        background-color: #f8f9fa;
        margin-bottom: 1rem;
    }
    
    #guardian_container:empty {
        display: none !important;
    }
    
    #guardian_container .text-info,
    #guardian_container .text-warning,
    #guardian_container .text-danger {
        margin: 0;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }
    
    #guardian_container .text-info {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
    }
    
    #guardian_container .text-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
    }
    
    #guardian_container .text-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }
    
    /* Modified field highlighting styles */
    .form-control.is-modified {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
        background-color: #fffbf0;
    }
    
    .form-control.is-modified:focus {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
    }
</style>

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
                        <label for="user_id" class="form-label">Patient ID</label>
                        <input type="text" class="form-control" id="user_id" name="user_id" 
                               pattern="PAT\d+" 
                               placeholder="e.g., PAT2025XXXXXXXX"
                               title="Please enter a valid patient ID (PAT followed by numbers)"
                               required>
                        <div class="invalid-feedback">Please enter a valid patient ID (PAT followed by numbers)</div>
                        <div class="form-text">Format: PAT followed by numbers (e.g., PAT2025XXXXXXXX)</div>
                        <div id="patient-validation-message" class="mt-2" style="display: none;"></div>
                    </div>
                    <div id="guardian_container" class="mb-3" style="display: none;">
                        <!-- Guardian selector will be dynamically added here -->
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Patient Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required readonly>
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
                        <label for="edit_user_id" class="form-label">Patient ID</label>
                        <input type="text" class="form-control" id="edit_user_id" name="user_id" readonly>
                    </div>
                    <div class="mb-3"> 
                        <label for="edit_patient_name" class="form-label">Patient Name</label>
                        <input type="text" class="form-control" id="edit_patient_name" name="full_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_guardian" class="form-label">Guardian</label>
                        <select class="form-control" id="edit_guardian" name="guardian">
                        </select>
                        <div class="form-text">Select the guardian who will accompany the patient</div>
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

