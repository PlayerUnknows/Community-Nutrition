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
    
    <link rel="stylesheet" href="../../assets/css/event.css">
</head>
<body>
    <main id="mainContent">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Event Management
                    </h5>
                                         <button type="button" class="btn btn-primary" id="addEventBtn">
                         <i class="fas fa-plus me-2"></i>Add Event
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
                                       id="eventSearch" 
                                       placeholder="Search events..."
                                       autocomplete="off"
                                       spellcheck="false">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="eventsPerPage">
                                <option value="5" selected>5 per page</option>
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <p id="events-showing-entries" class="text-muted mb-0 text-end">Showing 0 entries</p>
                        </div>
                    </div>

                    <div class="table-container mb-3">
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

                    <div class="row">
                        <div class="col-12">
                            <nav aria-label="Events pagination">
                                <ul class="pagination justify-content-end mb-0" id="eventsPagination">
                                    
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
    </main>

    <!-- Add Event Modal -->
    <div class="modal" id="addEventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                                 <div class="modal-header">
                     <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                     <button type="button" class="btn-close" aria-label="Close"></button>
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
                            <input type="time" class="form-control" id="event_time" name="event_time" min="06:00" max="17:00" required>
                            <div class="form-text text-muted">Event time must be between 6:00 AM and 5:00 PM</div>
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
                         <button type="button" class="btn btn-secondary">Close</button>
                         <button type="submit" class="btn btn-primary">Add Event</button>
                     </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal" id="editEventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                                 <div class="modal-header">
                     <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                     <button type="button" class="btn-close" aria-label="Close"></button>
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
                            <div class="invalid-feedback" id="edit_event_type_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_name" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="edit_event_name" name="event_name" required>
                            <div class="invalid-feedback" id="edit_event_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="edit_event_time" name="event_time" min="06:00" max="17:00" required>
                            <div class="form-text text-muted">Event time must be between 6:00 AM and 5:00 PM</div>
                            <div class="invalid-feedback" id="edit_event_time_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_place" class="form-label">Event Place</label>
                            <input type="text" class="form-control" id="edit_event_place" name="event_place" required>
                            <div class="invalid-feedback" id="edit_event_place_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="edit_event_date" name="event_date" required>
                            <div class="invalid-feedback" id="edit_event_date_error"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_min_age" class="form-label">Minimum Age</label>
                                    <input type="number" class="form-control" id="edit_min_age" name="min_age" min="0" required>
                                    <div class="invalid-feedback" id="edit_min_age_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_max_age" class="form-label">Maximum Age</label>
                                    <input type="number" class="form-control" id="edit_max_age" name="max_age" min="0" required>
                                    <div class="invalid-feedback" id="edit_max_age_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                                         <div class="modal-footer">
                         <button type="button" class="btn btn-secondary">Close</button>
                         <button type="submit" class="btn btn-primary">Update Event</button>
                     </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Event Modal -->
    <div class="modal" id="viewEventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                                 <div class="modal-header">
                     <h5 class="modal-title" id="viewEventModalLabel">Event Details</h5>
                     <button type="button" class="btn-close" aria-label="Close"></button>
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
                     <button type="button" class="btn btn-secondary">Close</button>
                 </div>
            </div>
        </div>
    </div>

    <style>
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .form-control.is-valid,
        .form-select.is-valid {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }
        
        .invalid-feedback.show {
            display: block;
        }
        
        .form-text {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            color: #6c757d;
        }
        
        /* Hide hint text when there's a validation error */
        .is-invalid + .form-text {
            display: none;
        }
    </style>

    <!-- Scripts -->
    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    
    <script src="../../node_modules/moment/min/moment.min.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    
  
    <script src="../script/event/add_event.js"></script>
    <script src="../script/event/event.js?v=<?php echo time(); ?>"></script>
    <script src="../script/event/edit_event.js"></script>
    <script src="../script/event/delete_event.js"></script>

    <!-- <script src="../script/event/fetch_all_event.js"></script> -->
    
   
</body>
</html> 
