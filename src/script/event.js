// Global variables for table state
var eventTable = null;

// Namespaced code
var eventManager = {
    init: function() {
        // Wait for document ready
        $(document).ready(function() {
            // Initialize DataTable
            const eventTable = $('#eventTable').DataTable({
                processing: true,
                serverSide: false,
                scrollX: true,
                scrollY: '50vh',
                scrollCollapse: true,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                pageLength: 10,
                ajax: {
                    url: '../backend/event_handler.php',
                    type: 'POST',
                    data: function(d) {
                        d.action = 'getAll';
                        return d;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables error:', error);
                        console.error('Server response:', xhr.responseText);
                    }
                },
                columns: [
                    { data: 'event_type', width: '120px' },
                    { data: 'event_name_created', width: '150px' },
                    { data: 'event_time', width: '100px' },
                    { data: 'event_place', width: '150px' },
                    { data: 'event_date', width: '120px' },
                    { 
                        data: 'created_by_name',
                        width: '150px',
                        render: function(data, type, row) {
                            return data || 'N/A';
                        }
                    },
                    { 
                        data: 'raw_edited_by',
                        width: '150px',
                        render: function(data, type, row) {
                            return data || 'N/A';
                        }
                    },
                    {
                        data: null,
                        width: '100px',
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary edit-event" data-id="${row.event_prikey}" title="Edit">
                                        <i class="far fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-event" data-id="${row.event_prikey}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[4, 'desc']], // Sort by date column by default
                language: {
                    processing: "Loading...",
                    emptyTable: "No events found",
                    zeroRecords: "No matching events found"
                }
            });

            // Function to safely hide modal
            function hideModal(modalId) {
                const modalElement = document.querySelector(modalId);
                if (!modalElement) return;

                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }

            // Add Event Form Submit
            $('#eventForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'add');

                $.ajax({
                    url: '../backend/event_handler.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Hide modal first
                        hideModal('#addEventModal');
                        $('#eventForm')[0].reset();

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message
                            }).then(() => {
                                eventTable.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide modal first
                        hideModal('#addEventModal');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while processing your request'
                        });
                    }
                });
            });

            // Edit Event Button Click
            $('#eventTable').on('click', '.edit-event', function() {
                const eventId = $(this).data('id');
                const row = eventTable.row($(this).closest('tr')).data();
                
                $('#editEventId').val(eventId);
                $('#editEventType').val(row.event_type);
                $('#editEventName').val(row.event_name_created);
                $('#editEventTime').val(row.event_time);
                $('#editEventPlace').val(row.event_place);
                $('#editEventDate').val(row.event_date);
                
                const editModal = new bootstrap.Modal($('#editEventModal'));
                editModal.show();
            });

            // Edit Event Form Submit
            $('#editEventForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'update');

                $.ajax({
                    url: '../backend/event_handler.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Hide modal first
                        hideModal('#editEventModal');
                        $('#editEventForm')[0].reset();

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message
                            }).then(() => {
                                eventTable.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide modal first
                        hideModal('#editEventModal');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while processing your request'
                        });
                    }
                });
            });

            // Delete Event Button Click
            $('#eventTable').on('click', '.delete-event', function() {
                const eventId = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../backend/event_handler.php',
                            type: 'POST',
                            data: {
                                action: 'delete',
                                event_id: eventId
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message
                                    }).then(() => {
                                        eventTable.ajax.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while processing your request'
                                });
                            }
                        });
                    }
                });
            });

            // Clear form and clean up when modal is hidden
            $('#addEventModal').on('hidden.bs.modal', function() {
                $('#eventForm')[0].reset();
            });

            $('#editEventModal').on('hidden.bs.modal', function() {
                $('#editEventForm')[0].reset();
            });

            // Handle ESC key to properly close modals
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideModal('#addEventModal');
                    hideModal('#editEventModal');
                }
            });
        });
    }
};

// Initialize the event manager
eventManager.init();