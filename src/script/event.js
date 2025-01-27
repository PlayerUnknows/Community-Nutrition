$(document).ready(function() {
    // Initialize Bootstrap modals
    const addEventModal = new bootstrap.Modal(document.getElementById('addEventModal'));
    const editEventModal = new bootstrap.Modal(document.getElementById('editEventModal'));

    // Helper function to hide modal
    function hideModal(modalId) {
        try {
            const modalEl = document.querySelector(modalId);
            if (!modalEl) return;
            
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
                // Wait for modal to finish hiding before cleanup
                modalEl.addEventListener('hidden.bs.modal', function() {
                    document.querySelector('.modal-backdrop')?.remove();
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                }, { once: true });
            } else {
                // Clean up if no modal instance found
                document.querySelector('.modal-backdrop')?.remove();
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            }
        } catch (error) {
            console.error('Error hiding modal:', error);
            // Fallback cleanup
            document.querySelector('.modal-backdrop')?.remove();
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
        }
    }

    // Helper function to sanitize input
    function sanitizeInput(input) {
        if (typeof input !== 'string') return input;
        return input.replace(/[<>]/g, '');
    }

    // Initialize DataTable
    let eventTable = $('#eventTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: '../backend/event_handler.php',
            type: 'POST',
            dataSrc: function(response) {
                // Check if response has data property
                return response.data || [];
            },
            data: function(d) {
                return {
                    ...d,
                    action: 'getAll'
                };
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                console.error('Server response:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load event data'
                });
            }
        },
        columns: [
            { data: 'event_type', defaultContent: '' },
            { data: 'event_name_created', defaultContent: '' },
            { data: 'event_time', defaultContent: '' },
            { data: 'event_place', defaultContent: '' },
            { data: 'event_date', defaultContent: '' },
            { data: 'created_by_name', defaultContent: '' },
            { data: 'edited_by', defaultContent: '' },
            { 
                data: 'raw_created_at',
                defaultContent: '',
                render: function(data) {
                    return data ? moment(data).format('MMM D, YYYY HH:mm') : '';
                }
            },
            {
                data: 'event_prikey',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary edit-event" data-id="${data}">
                                <i class="far fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-event" data-id="${data}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[7, 'desc']], // Order by created_at column
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            emptyTable: "No events found",
            zeroRecords: "No matching events found",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            search: "Search:",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Add Event Form Submit
    $('#addEventForm').on('submit', function(e) {
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
                addEventModal.hide();
                $('#addEventForm')[0].reset();

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
                        text: response.message || 'An error occurred'
                    });
                }
            },
            error: function(xhr, status, error) {
                addEventModal.hide();
                
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
        
        editEventModal.show();
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
                editEventModal.hide();
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
                        text: response.message || 'An error occurred'
                    });
                }
            },
            error: function(xhr, status, error) {
                editEventModal.hide();
                
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
                                text: response.message || 'An error occurred'
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

    // Handle entries per page change
    $('#eventsPerPage').on('change', function() {
        eventTable.page.len($(this).val()).draw();
    });

    // Handle search
    $('#eventSearch').on('keyup', function() {
        eventTable.search(this.value).draw();
    });

    // Handle modal cleanup
    $('#addEventModal, #editEventModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

    // Handle ESC key to properly close modals
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            hideModal('#addEventModal');
            hideModal('#editEventModal');
        }
    });
});