<?php require_once '../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.min.css">
    
    <style>
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            display: none !important;
        }
        .custom-controls {
            margin-bottom: 1rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        #eventTable_wrapper {
            padding: 0;
        }
        .pagination {
            margin: 0;
        }
    </style>
</head>
<body>
    <main id="mainContent">
        <div class="container-fluid mt-4">
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
                    <!-- Custom controls -->
                    <div class="row mb-3">
                        <div class="col-md-6 d-flex align-items-center">
                            <label class="me-2">Show</label>
                            <select class="form-select w-auto" id="eventsPerPage">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <label class="ms-2">entries</label>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="search" 
                                       class="form-control" 
                                       id="eventSearch" 
                                       placeholder="Search events..."
                                       autocomplete="off"
                                       spellcheck="false">
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
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
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addEventForm">
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
                    <input type="hidden" id="edit_event_id" name="event_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_event_type" class="form-label">Event Type</label>
                            <select class="form-select" id="edit_event_type" name="event_type" required>
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
                            <label for="edit_event_name" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="edit_event_name" name="event_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="edit_event_time" name="event_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_place" class="form-label">Event Place</label>
                            <input type="text" class="form-control" id="edit_event_place" name="event_place" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="edit_event_date" name="event_date" required>
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

    <!-- Scripts -->
    <script src="../../node_modules/moment/min/moment.min.js"></script>
    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="../script/event.js"></script>
</body>
</html>
