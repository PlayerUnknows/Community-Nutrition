$(document).ready(function() {
    // Initialize Bootstrap modals
    const addEventModal = new bootstrap.Modal(document.getElementById('addEventModal'));
    const editEventModal = new bootstrap.Modal(document.getElementById('editEventModal'));

    // Initialize DataTable with simplified configuration
    let eventTable = $('#eventTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '../backend/event_handler.php',
            type: 'POST',
            data: { action: 'getAll' }
        },
        columns: [
            { data: 'event_type' },
            { data: 'event_name_created' },
            { data: 'event_time' },
            { data: 'event_place' },
            { data: 'event_date' },
            { data: 'created_by_name' },
            { data: 'edited_by' },
            { data: 'raw_created_at' },
            { data: 'raw_updated_at' },
            {
                data: 'event_prikey',
                render: function(data, type, row) {
                    return `
                        <button onclick="editEvent(${data})" class="btn btn-sm btn-outline-primary">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event" data-id="${data}">
                            Delete
                        </button>
                    `;
                }
            }
        ]
    });

    // Define editEvent function in global scope
    window.editEvent = function(eventId) {

        // Get the data from DataTable
        const data = eventTable.rows().data().toArray()
            .find(row => row.event_prikey == eventId);
            
        if (data) {
            // Populate form fields
            $('#edit_event_prikey').val(data.event_prikey);
            $('#edit_event_type').val(data.event_type);
            $('#edit_event_name').val(data.event_name_created);
            $('#edit_event_time').val(data.event_time);
            $('#edit_event_place').val(data.event_place);
            $('#edit_event_date').val(data.event_date);

            // Show modal
            editEventModal.show();
        } else {
            console.error('No data found for ID:', eventId);
        }
    };

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

    // Edit Event Form Submit
    $('#editEventForm').on('submit', function(e) {
        e.preventDefault();
        
        // Create FormData object
        const formData = new FormData(this);
        formData.append('action', 'update');

        // Debug: Log all form data
        console.log('Form Data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // Validate required fields
        const requiredFields = ['event_prikey', 'event_type', 'event_name', 'event_time', 'event_place', 'event_date'];
        let missingFields = [];
        
        requiredFields.forEach(field => {
            if (!formData.get(field)) {
                missingFields.push(field);
            }
        });

        if (missingFields.length > 0) {
            console.error('Missing required fields:', missingFields);
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields: ' + missingFields.join(', ')
            });
            return;
        }

        $.ajax({
            url: '../backend/event_handler.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Success Response:', response);
                if (response.success) {
                    editEventModal.hide();
                    $('#editEventForm')[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then(() => {
                        eventTable.ajax.reload(null, false);
                    });
                } else {
                    console.error('Error in response:', response);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An error occurred'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON,
                    statusText: xhr.statusText
                });
                
                let errorMessage = 'An error occurred while processing your request';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.error || errorMessage;
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                
                editEventModal.hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
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