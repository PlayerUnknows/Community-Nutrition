    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Event management system with Bootstrap 5.3.3 and SweetAlert2">
        <title>Event Management</title>

        <!-- Bootstrap CSS -->
        <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link href="../../assets/fontawesome-free-5.15.4-web/css/all.css" rel="stylesheet">

        <!-- SweetAlert2 CSS -->
        <link href="../../assets/css/sweetalert2.css" rel="stylesheet">

        <!-- Custom CSS -->
        <style>
            body {
                background-color: white;
            }

            .table thead {
                background-color: #0d6efd;
                color: white;
            }

            .modal-header {
                background-color: #0d6efd;
                color: white;
            }

            .btn-close-white {
                filter: invert(1);
            }

            .toast {
                position: fixed;
                bottom: 1rem;
                right: 1rem;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .table-responsive {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }

                .modal-dialog {
                    max-width: 100%;
                    margin: 0;
                }

                .modal-content {
                    border-radius: 0;
                }
            }
        </style>
    </head>

    <body>
        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <main class="col-md-11 col-lg-15 px-6">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4 border-bottom bg-white px-3 shadow-sm">
                        <h1 class="h2 text-primary">Event Management</h1>
                        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fa fa-plus me-2"></i>Add New Event
                        </button>
                    </div>

                    <!-- Event List Table -->
                    <div class="table-responsive bg-white rounded shadow-sm p-3" id="event">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Event Type</th>
                                    <th>Event Name</th>
                                    <th>Time</th>
                                    <th>Place</th>
                                    <th>Date</th>
                                    <th>Created By</th>
                                    <th>Event Creator</th>
                                    <th>Edited By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                <?php
                                require_once('../models/event_model.php');
                                $events = getAllEvents();
                                foreach ($events as $event) {
                                    echo "<tr>";
                                    echo "<td>{$event['event_type']}</td>";
                                    echo "<td>{$event['event_name_created']}</td>";
                                    echo "<td>" . date('h:i A', strtotime($event['event_time'])) . "</td>";
                                    echo "<td>{$event['event_place']}</td>";
                                    echo "<td>" . date('M d, Y', strtotime($event['event_date'])) . "</td>";
                                    echo "<td>{$event['created_by']}</td>";
                                    echo "<td>{$event['event_creator_email']}</td>";
                                    echo "<td>{$event['event_editor_email']}</td>";
                                    echo "<td>
                                        <button class='btn btn-sm btn-outline-primary me-1' onclick='editEvent({$event['event_id']}, \"{$event['event_type']}\", \"{$event['event_name_created']}\", \"" . date('H:i', strtotime($event['event_time'])) . "\", \"" . date('Y-m-d', strtotime($event['event_date'])) . "\", \"{$event['event_place']}\")'>
                                            <i class='far fa-edit'></i>
                                        </button>
                                        <button class='btn btn-sm btn-outline-danger' onclick='confirmDeleteEvent({$event['event_id']})'>
                                            <i class='fa fa-trash'></i>
                                        </button>
                                    </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Add Event Modal -->
                    <div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="addEventModalLabel">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addEventModalLabel"><i class="fa fa-calendar-plus me-2"></i>Add New Event</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="eventForm" method="POST" onsubmit="addEvent(this); return false;">
                                    <input type="hidden" name="action" value="add">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="eventType" class="form-label">Event Type</label>
                                            <select class="form-select" id="eventType" name="event_type" required>
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
                                            <label for="eventName" class="form-label">Event Name</label>
                                            <input type="text" class="form-control" id="eventName" name="event_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="eventTime" class="form-label">Event Time</label>
                                            <input type="time" class="form-control" id="eventTime" name="event_time" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="eventPlace" class="form-label">Event Place</label>
                                            <input type="text" class="form-control" id="eventPlace" name="event_place" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="eventDate" class="form-label">Event Date</label>
                                            <input type="date" class="form-control" id="eventDate" name="event_date" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <label for="ageRangeMin" class="form-label">Minimum Age</label>
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    id="ageRangeMin"
                                                    name="age_range_min"
                                                    placeholder="Min Age"
                                                    min="0"
                                                    max="100">
                                            </div>
                                            <div class="col-6">
                                                <label for="ageRangeMax" class="form-label">Maximum Age</label>
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    id="ageRangeMax"
                                                    name="age_range_max"
                                                    placeholder="Max Age"
                                                    min="0"
                                                    max="100">
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Enter age range for event participants</small>
                                    </div>

                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Event</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Event Modal -->
                    <div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="editEventModalLabel">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editEventModalLabel"><i class="fa fa-calendar-plus me-2"></i>Edit Event</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="editEventForm" method="POST" onsubmit="updateEvent(this); return false;">
                                    <input type="hidden" id="edit_event_id" name="event_id">
                                    <input type="hidden" name="action" value="edit">
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
                                    
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Event</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Initial load of events when the event tab is shown
                        document.addEventListener('DOMContentLoaded', function() {
                            // Listen for tab show event
                            $('button[data-bs-target="#event"]').on('shown.bs.tab', function(e) {
                                console.log('Event tab shown, refreshing events...');
                                refreshEvents();
                            });

                            // If we're already on the events tab, load the events
                            if (document.querySelector('#event.active')) {
                                console.log('Event tab is active, loading events...');
                                refreshEvents();
                            }
                        });

                        function refreshEvents() {
                            console.log('Starting refresh...');
                            const tbody = document.querySelector('#event table tbody');
                            if (!tbody) {
                                console.error('Could not find table body element');
                                return;
                            }

                            fetch('../controllers/event_controller.php?action=getAll')
                                .then(response => {
                                    console.log('Response received:', response.status);
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.text().then(text => {
                                        console.log('Raw response:', text);
                                        try {
                                            return JSON.parse(text);
                                        } catch (e) {
                                            console.error('JSON parse error:', e);
                                            throw new Error('Invalid JSON response');
                                        }
                                    });
                                })
                                .then(events => {
                                    console.log('Processing events:', events);
                                    if (!Array.isArray(events)) {
                                        console.error('Expected array of events, got:', typeof events);
                                        throw new Error('Invalid data format');
                                    }

                                    // Clear the table
                                    tbody.innerHTML = '';

                                    // Add each event
                                    events.forEach(event => {
                                        try {
                                            const row = document.createElement('tr');
                                            const eventTime = event.event_time ? new Date(`1970-01-01T${event.event_time}`) : new Date();
                                            const eventDate = event.event_date ? new Date(event.event_date) : new Date();

                                            row.innerHTML = `
                                                <td>${event.event_type || ''}</td>
                                                <td>${event.event_name_created || ''}</td>
                                                <td>${eventTime.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</td>
                                                <td>${event.event_place || ''}</td>
                                                <td>${eventDate.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</td>
                                                <td>${event.created_by || ''}</td>
                                                <td>${event.event_creator_email || ''}</td>
                                                <td>${event.event_editor_email || ''}</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-primary me-1' onclick='editEvent(${event.event_id}, "${event.event_type || ''}", "${event.event_name_created || ''}", "${event.event_time ? event.event_time.substring(0, 5) : ''}", "${event.event_date || ''}")'>
                                                        <i class='far fa-edit'></i>
                                                    </button>
                                                    <button class='btn btn-sm btn-outline-danger' onclick='confirmDeleteEvent(${event.event_id})'>
                                                        <i class='fa fa-trash'></i>
                                                    </button>
                                                </td>
                                            `;
                                            tbody.appendChild(row);
                                        } catch (e) {
                                            console.error('Error processing event:', event, e);
                                        }
                                    });
                                    console.log('Table updated successfully');
                                })
                                .catch(error => {
                                    console.error('Error in refreshEvents:', error);
                                    Swal.fire('Error!', 'Failed to refresh events. Check console for details.', 'error');
                                });
                        }

                        function confirmDeleteEvent(eventId) {
                            if (!eventId) {
                                console.error('No event ID provided');
                                return;
                            }

                            Swal.fire({
                                title: 'Are you sure?',
                                text: "You won't be able to revert this!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes, delete it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    deleteEvent(eventId);
                                }
                            });
                        }

                        function deleteEvent(eventId) {
                            console.log('Deleting event:', eventId);
                            fetch(`../controllers/event_controller.php?action=delete&id=${eventId}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Deleted!',
                                            text: 'Your event has been deleted.',
                                            icon: 'success',
                                            didClose: () => {
                                                refreshEvents();
                                            }
                                        });
                                    } else {
                                        throw new Error(data.error || 'Failed to delete the event');
                                    }
                                })
                                .catch(error => {
                                    console.error('Delete error:', error);
                                    Swal.fire('Error!', 'Failed to delete the event.', 'error');
                                });
                        }

                        function editEvent(eventId, eventType, eventName, eventTime, eventDate) {
                            console.log('Editing event:', {
                                eventId,
                                eventType,
                                eventName,
                                eventTime,
                                eventDate
                            });

                            // Set values in the edit form
                            document.getElementById('edit_event_id').value = eventId;
                            document.getElementById('edit_event_type').value = eventType;
                            document.getElementById('edit_event_name').value = eventName;
                            document.getElementById('edit_event_time').value = eventTime;
                            document.getElementById('edit_event_date').value = eventDate;

                            // Show the edit modal
                            $('#editEventModal').modal('show');
                        }

                        function updateEvent(formElement) {
                            event.preventDefault();
                            console.log('Updating event...');

                            const formData = new FormData(formElement);
                            formData.append('action', 'edit');

                            fetch('../controllers/event_controller.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        $('#editEventModal').modal('hide');
                                        Swal.fire({
                                            title: 'Updated!',
                                            text: 'Event has been updated successfully.',
                                            icon: 'success',
                                            didClose: () => {
                                                refreshEvents();
                                                formElement.reset();
                                            }
                                        });
                                    } else {
                                        throw new Error(data.error || 'Failed to update the event');
                                    }
                                })
                                .catch(error => {
                                    console.error('Update error:', error);
                                    Swal.fire('Error!', 'Failed to update the event.', 'error');
                                });
                        }

                        function addEvent(formElement) {
                            event.preventDefault();
                            console.log('Adding new event...');

                            const formData = new FormData(formElement);
                            formData.append('action', 'add');

                            fetch('../controllers/event_controller.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        $('#addEventModal').modal('hide');

                                        Swal.fire({
                                            title: 'Success!',
                                            text: 'Event has been added successfully.',
                                            icon: 'success',
                                            didClose: () => {
                                                refreshEvents();
                                                formElement.reset();
                                            }
                                        });
                                    } else {
                                        throw new Error(data.error || 'Failed to add the event');
                                    }
                                })
                                .catch(error => {
                                    console.error('Add error:', error);
                                    Swal.fire('Error!', 'Failed to add the event.', 'error');
                                });
                        }
                        $('#addEventModal').on('hidden.bs.modal', function(e) {
                            $('.modal-backdrop').remove();

                        })
                    </script>

                    <!-- Bootstrap JS -->
                    <script src="../../assets/dist/bootstrap.min.js"></script>
                    <script src="../../assets/dist/popper.js"></script>

                    <script src="../../assets/dist/sweetalert.js"></script>
                </main>
            </div>
        </div>
    </body>

    </html>