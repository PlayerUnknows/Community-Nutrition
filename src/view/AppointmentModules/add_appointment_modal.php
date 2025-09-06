<?php

?>

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