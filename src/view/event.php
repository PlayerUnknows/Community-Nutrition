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
        
        /* Add these new styles */
        #eventTable th, 
        #eventTable td {
            white-space: normal;
            word-wrap: break-word;
            min-width: 100px; /* Minimum width for draggable columns */
            max-width: 300px; /* Maximum width to prevent excessive stretching */
        }

        #eventTable td {
            vertical-align: middle;
        }

        .table-responsive {
            overflow-x: auto;
            /* Enable smooth scrolling on iOS */
            -webkit-overflow-scrolling: touch;
        }
        
        .card {
            margin: 0 !important;
            width: 100% !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
        
        .table-responsive {
            width: 100% !important;
            margin: 0 !important;
        }
        
        #eventTable {
            width: 100% !important;
        }

        /* Style for draggable columns */
        .dragging {
            opacity: 0.5;
            background-color: #f8f9fa;
        }

        /* Custom width for specific columns */
        #eventTable th:nth-child(1), /* Event Type */
        #eventTable td:nth-child(1) {
            min-width: 120px;
        }

        #eventTable th:nth-child(2), /* Event Name */
        #eventTable td:nth-child(2) {
            min-width: 150px;
        }

        #eventTable th:nth-child(3), /* Time */
        #eventTable td:nth-child(3) {
            min-width: 100px;
        }

        #eventTable th:nth-child(4), /* Place */
        #eventTable td:nth-child(4) {
            min-width: 150px;
        }

        #eventTable th:nth-child(5), /* Date */
        #eventTable td:nth-child(5) {
            min-width: 100px;
        }

        #eventTable th:nth-child(6), /* Age Range */
        #eventTable td:nth-child(6) {
            min-width: 100px;
        }

        #eventTable th:last-child, /* Actions */
        #eventTable td:last-child {
            min-width: 120px;
            text-align: center;
        }
    </style>
</head>
<body>
    <main id="mainContent">
        <div class="w-100 mt-4" style="padding: 0; margin: 0;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Event Management
                    </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-2"></i>Add Event
                    </button>
                </div>
                <div class="card-body p-0">
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
                                    <th>Time</th>
                                    <th>Place</th>
                                    <th>Date</th>
                                    <th>Age Range</th>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_age" class="form-label">Minimum Age</label>
                                    <input type="number" class="form-control" id="min_age" name="min_age" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_age" class="form-label">Maximum Age</label>
                                    <input type="number" class="form-control" id="max_age" name="max_age" min="0" required>
                                </div>
                            </div>
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
                    <input type="hidden" id="edit_event_prikey" name="event_prikey">
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_min_age" class="form-label">Minimum Age</label>
                                    <input type="number" class="form-control" id="edit_min_age" name="min_age" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_max_age" class="form-label">Maximum Age</label>
                                    <input type="number" class="form-control" id="edit_max_age" name="max_age" min="0" required>
                                </div>
                            </div>
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

    <!-- View Event Modal -->
    <div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewEventModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Created By</th>
                                    <td id="view_created_by"></td>
                                </tr>
                                <tr>
                                    <th>Edited By</th>
                                    <td id="view_edited_by"></td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td id="view_created_at"></td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td id="view_updated_at"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    
    <script src="../../node_modules/moment/min/moment.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    
  
    <script src="../script/event.js?v=<?php echo time(); ?>"></script>
    
   
</body>
</html>
