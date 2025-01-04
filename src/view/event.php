<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.min.css">
    
    <style>
        .table-responsive {
            margin-top: 15px;
        }
        .modal-dialog {
            max-width: 600px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Event Management
            </h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                <i class="fas fa-plus me-2"></i>Add Event
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="eventTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Event Type</th>
                            <th>Event Name</th>
                            <th>Event Time</th>
                            <th>Event Place</th>
                            <th>Event Date</th>
                            <th>Created By</th>
                            <th>Editor Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="event_type" class="form-label">Event Type</label>
                        <select class="form-select" id="event_type" name="event_type" required>
                            <option value="">Select Event Type</option>
                            <option value="Deworming">National Deworming Month</option>
                            <option value="Vitamin A">Vitamin A Distribution</option>
                            <option value="Operation Timbang">Operation Timbang</option>
                            <option value="Garantisadong Pambata">Garantisadong Pambata</option>
                            <option value="Immunization">Immunization Program</option>
                            <option value="Nutrition Month">Nutrition Month Celebration</option>
                            <option value="Feeding Program">Feeding Program</option>
                            <option value="Health Education">Health Education Session</option>
                            <option value="Medical Mission">Medical Mission</option>
                            <option value="Other">Other Health Programs</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="event_name" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="event_name" name="event_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_time" class="form-label">Event Time</label>
                        <input type="time" class="form-control" id="event_time" name="event_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_place" class="form-label">Event Place</label>
                        <input type="text" class="form-control" id="event_place" name="event_place" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_date" class="form-label">Event Date</label>
                        <input type="date" class="form-control" id="event_date" name="event_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Age Range</label>
                        <div class="row">
                            <div class="col-6">
                                <label for="age_range_min" class="form-label">Minimum Age</label>
                                <input type="number" class="form-control" id="age_range_min" name="age_range_min" placeholder="Min Age" min="0" max="100">
                            </div>
                            <div class="col-6">
                                <label for="age_range_max" class="form-label">Maximum Age</label>
                                <input type="number" class="form-control" id="age_range_max" name="age_range_max" placeholder="Max Age" min="0" max="100">
                            </div>
                        </div>
                        <small class="form-text text-muted">Enter age range for event participants (optional)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEventForm">
                <input type="hidden" id="editEventId" name="event_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editEventType" class="form-label">Event Type</label>
                        <select class="form-select" id="editEventType" name="event_type" required>
                            <option value="">Select Event Type</option>
                            <option value="Deworming">National Deworming Month</option>
                            <option value="Vitamin A">Vitamin A Distribution</option>
                            <option value="Operation Timbang">Operation Timbang</option>
                            <option value="Garantisadong Pambata">Garantisadong Pambata</option>
                            <option value="Immunization">Immunization Program</option>
                            <option value="Nutrition Month">Nutrition Month Celebration</option>
                            <option value="Feeding Program">Feeding Program</option>
                            <option value="Health Education">Health Education Session</option>
                            <option value="Medical Mission">Medical Mission</option>
                            <option value="Other">Other Health Programs</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editEventName" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="editEventName" name="event_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventTime" class="form-label">Event Time</label>
                        <input type="time" class="form-control" id="editEventTime" name="event_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventPlace" class="form-label">Event Place</label>
                        <input type="text" class="form-control" id="editEventPlace" name="event_place" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventDate" class="form-label">Event Date</label>
                        <input type="date" class="form-control" id="editEventDate" name="event_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Age Range</label>
                        <div class="row">
                            <div class="col-6">
                                <label for="editAgeRangeMin" class="form-label">Minimum Age</label>
                                <input type="number" class="form-control" id="editAgeRangeMin" name="age_range_min" placeholder="Min Age" min="0" max="100">
                            </div>
                            <div class="col-6">
                                <label for="editAgeRangeMax" class="form-label">Maximum Age</label>
                                <input type="number" class="form-control" id="editAgeRangeMax" name="age_range_max" placeholder="Max Age" min="0" max="100">
                            </div>
                        </div>
                        <small class="form-text text-muted">Enter age range for event participants (optional)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Required Scripts -->
<script src="../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="../../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>

<!-- Custom Scripts -->
<script src="../script/event.js"></script>

</body>
</html>