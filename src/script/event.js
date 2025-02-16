$(document).ready(function() {
    // Initialize Bootstrap modals
    const addEventModal = new bootstrap.Modal(document.getElementById('addEventModal'));
    const editEventModal = new bootstrap.Modal(document.getElementById('editEventModal'));

    // Initialize DataTable with simplified configuration
    let eventTable = $('#eventTable').DataTable({
        processing: true,
        serverSide: false,
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        order: [[4, 'desc']],
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
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.min_age} - ${row.max_age}`;
                },
                title: 'Age Range'
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button onclick="viewEventDetails(${row.event_prikey})" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editEvent(${row.event_prikey})" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event" data-id="${row.event_prikey}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // Define editEvent function in global scope
    window.editEvent = function(eventId) {
        const data = eventTable.rows().data().toArray()
            .find(row => row.event_prikey == eventId);
            
        if (data) {
            // Get modal element
            const modalEl = document.getElementById('editEventModal');
            
            // Dispose any existing modal instance
            const existingModal = bootstrap.Modal.getInstance(modalEl);
            if (existingModal) {
                existingModal.dispose();
            }
            
            // Create new modal instance
            const editEventModal = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });
            
            // Set form values
            $('#edit_event_prikey').val(data.event_prikey);
            $('#edit_event_type').val(data.event_type);
            $('#edit_event_name').val(data.event_name_created);
            $('#edit_event_time').val(data.event_time);
            $('#edit_event_place').val(data.event_place);
            $('#edit_event_date').val(data.event_date);
            $('#edit_min_age').val(data.min_age);
            $('#edit_max_age').val(data.max_age);

            // Show the modal
            editEventModal.show();
        } else {
            console.error('No data found for ID:', eventId);
        }
    };

    // Add this new function for viewing event details
    window.viewEventDetails = function(eventId) {
        const data = eventTable.rows().data().toArray()
            .find(row => row.event_prikey == eventId);
            
        if (data) {
            // Populate modal with event details
            $('#view_created_by').text(data.created_by_name);
            $('#view_edited_by').text(data.edited_by || 'N/A');
            $('#view_created_at').text(data.raw_created_at);
            $('#view_updated_at').text(data.raw_updated_at || 'N/A');
            
            // Show the modal
            const viewEventModal = new bootstrap.Modal(document.getElementById('viewEventModal'));
            viewEventModal.show();
        }
    };

    // Helper function to hide modal
    function hideModal(modalId) {
        try {
            const modalEl = document.querySelector(modalId);
            if (!modalEl) return;
            
            // Create a promise to handle modal hiding
            const hideModalPromise = new Promise((resolve) => {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modalEl.addEventListener('hidden.bs.modal', function handler() {
                        modalEl.removeEventListener('hidden.bs.modal', handler);
                        resolve();
                    });
                    modal.hide();
                } else {
                    resolve();
                }
            });

            // After modal is hidden, clean up
            hideModalPromise.then(() => {
                // Clean up modal elements
                if (modalEl) {
                    modalEl.style.cssText = 'display: none !important';
                    modalEl.classList.remove('show', 'modal-open', 'fade');
                    modalEl.setAttribute('aria-hidden', 'true');
                    modalEl.removeAttribute('aria-modal');
                    modalEl.removeAttribute('role');
                }

                // Super aggressive backdrop removal
                const removeBackdrops = () => {
                    document.querySelectorAll('.modal-backdrop').forEach(el => {
                        if (el) {
                            el.style.cssText = 'display: none !important';
                            el.remove();
                            if (el.parentElement) {
                                el.parentElement.removeChild(el);
                            }
                        }
                    });

                    const backdrops = document.getElementsByClassName('modal-backdrop');
                    while (backdrops.length > 0 && backdrops[0]) {
                        backdrops[0].style.cssText = 'display: none !important';
                        backdrops[0].remove();
                        if (backdrops[0] && backdrops[0].parentElement) {
                            backdrops[0].parentElement.removeChild(backdrops[0]);
                        }
                    }
                };

                // Clean up body
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.body.style.overflow = '';

                // Remove modal-open class from all elements
                document.querySelectorAll('.modal-open').forEach(el => {
                    if (el) {
                        el.classList.remove('modal-open');
                    }
                });

                // Execute backdrop removal multiple times
                removeBackdrops();
                setTimeout(removeBackdrops, 50);
                setTimeout(removeBackdrops, 150);

                // Dispose modal instance after cleanup
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.dispose();
                }
            });

        } catch (error) {
            console.error('Error hiding modal:', error);
            // Fallback aggressive cleanup
            document.querySelectorAll('.modal-backdrop').forEach(el => {
                if (el) {
                    el.style.cssText = 'display: none !important';
                    el.remove();
                }
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.overflow = '';
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
                hideModal('#addEventModal');
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
                hideModal('#addEventModal');
                
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
        const requiredFields = ['event_prikey', 'event_type', 'event_name', 'event_time', 
                               'event_place', 'event_date', 'min_age', 'max_age'];
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
                    hideModal('#editEventModal');
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
                
                hideModal('#editEventModal');
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
                        event_prikey: eventId
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