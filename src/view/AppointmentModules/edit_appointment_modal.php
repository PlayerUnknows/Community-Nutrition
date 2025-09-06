<?php

?>

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