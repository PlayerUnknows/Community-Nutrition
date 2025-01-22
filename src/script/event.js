// Global variables for table state
var eventTable = null;

// Security utility functions
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) {
        return '';
    }
    // Convert to string if it's not already a string
    unsafe = String(unsafe);
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function sanitizeInput(input) {
    if (!input || typeof input !== 'string') return '';
    
    // Remove HTML tags and special characters
    input = input.replace(/<[^>]*>/g, '');
    
    // Remove potentially dangerous patterns
    input = input.replace(/(javascript|vbscript|expression|applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base|alert|onload|onunload|onchange|onsubmit|onreset|onselect|onblur|onfocus|onabort|onkey|onmouse|onclick|ondblclick|onerror|onresize|onscroll)\s*:/gi, '');
    
    // Remove escaped characters and entities
    input = input.replace(/&[#\w]+;/g, '');
    input = input.replace(/\\x[0-9a-f]+/gi, '');
    input = input.replace(/\\/g, '');
    
    return input.trim();
}

function validateInput(input) {
    if (!input || typeof input !== 'string') {
        return false;
    }

    // Check length
    if (input.length > 255) {
        return false;
    }

    // Check for potentially dangerous patterns
    const dangerousPatterns = [
        /<[^>]*>/,              // HTML tags
        /javascript:/i,         // JavaScript protocol
        /data:\s*[^\s]*/i,     // Data URLs
        /on\w+\s*=/i,          // Event handlers
        /\b(alert|confirm|prompt|eval|setTimeout|setInterval)\s*\(/i, // JavaScript functions
        /&#x?[0-9a-f]+;?/i,    // Hex entities
        /\\x[0-9a-f]+/i,       // Hex escape sequences
        /\\\\/                  // Backslashes
    ];

    return !dangerousPatterns.some(pattern => pattern.test(input));
}

// Namespaced code
var eventManager = {
    init: function() {
        // Wait for document ready
        $(document).ready(function() {
            // Prevent paste of potentially dangerous content
            $('input[type="text"], textarea').on('paste', function(e) {
                e.preventDefault();
                const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                const sanitized = sanitizeInput(text);
                if (validateInput(sanitized)) {
                    document.execCommand('insertText', false, sanitized);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'The pasted content contains potentially dangerous characters'
                    });
                }
            });

            // Form validation before submit
            $('#eventForm, #editEventForm').on('submit', function(e) {
                e.preventDefault();
                const inputs = $(this).find('input[type="text"], textarea');
                let isValid = true;
                
                inputs.each(function() {
                    const value = $(this).val();
                    const sanitized = sanitizeInput(value);
                    
                    if (!validateInput(sanitized)) {
                        isValid = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Input',
                            text: 'Please remove any special characters, HTML tags, or potentially dangerous content'
                        });
                        return false;
                    }
                    
                    // Update with sanitized value
                    $(this).val(sanitized);
                });
                
                if (!isValid) {
                    return false;
                }
                
                // Continue with form submission
                const formData = new FormData(this);
                $.ajax({
                    url: '../backend/event_handler.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'An error occurred'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while processing your request'
                        });
                    }
                });
            });

            // Sanitize inputs on blur
            $('input[type="text"], textarea').on('blur', function() {
                const value = $(this).val();
                const sanitized = sanitizeInput(value);
                if (validateInput(sanitized)) {
                    $(this).val(sanitized);
                } else {
                    $(this).val('');
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Input',
                        text: 'The input contains potentially dangerous characters and has been cleared'
                    });
                }
            });

            // Initialize DataTable
            const eventTable = $('#eventTable').DataTable({
                processing: true,
                serverSide: false,
                scrollX: true,
                scrollY: '50vh',
                scrollCollapse: true,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                pageLength: 5,
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
                    { 
                        data: 'event_type',
                        width: '120px',
                        render: function(data) {
                            return escapeHtml(data || '');
                        }
                    },
                    { 
                        data: 'event_name_created',
                        width: '150px',
                        render: function(data) {
                            return escapeHtml(data || '');
                        }
                    },
                    { 
                        data: 'event_time',
                        width: '100px',
                        render: function(data) {
                            return escapeHtml(data || '');
                        }
                    },
                    { 
                        data: 'event_place',
                        width: '150px',
                        render: function(data) {
                            return escapeHtml(data || '');
                        }
                    },
                    { 
                        data: 'event_date',
                        width: '120px',
                        render: function(data) {
                            return escapeHtml(data || '');
                        }
                    },
                    { 
                        data: 'created_by_name',
                        width: '150px',
                        render: function(data) {
                            return escapeHtml(data || 'N/A');
                        }
                    },
                    { 
                        data: 'raw_edited_by',
                        width: '150px',
                        render: function(data) {
                            return escapeHtml(data || 'N/A');
                        }
                    },
                    {
                        data: null,
                        width: '100px',
                        render: function(data, type, row) {
                            const eventId = escapeHtml(row.event_prikey);
                            return `
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary edit-event" data-id="${eventId}" title="Edit">
                                        <i class="far fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-event" data-id="${eventId}" title="Delete">
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

                // Remove backdrop and modal-open class first
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.style.backgroundColor = 'rgba(173, 216, 230, 0.5)'; // Baby blue with transparency
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
                
                // Then dispose of the modal
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.dispose();
                }
            }

            // Style modal backdrops when they're created
            $(document).on('show.bs.modal', '.modal', function() {
                setTimeout(() => {
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.backgroundColor = 'rgba(173, 216, 230, 0.5)'; // Baby blue with transparency
                    }
                    
                    // Also style the modal content
                    this.querySelector('.modal-content').style.backgroundColor = '#e6f3ff'; // Lighter baby blue for modal content
                }, 0);
            });

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
                $('#editEventType').val(sanitizeInput(row.event_type));
                $('#editEventName').val(sanitizeInput(row.event_name_created));
                $('#editEventTime').val(sanitizeInput(row.event_time));
                $('#editEventPlace').val(sanitizeInput(row.event_place));
                $('#editEventDate').val(sanitizeInput(row.event_date));
                
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
                document.querySelector('.modal-backdrop')?.remove();
                document.body.classList.remove('modal-open');
            });

            $('#editEventModal').on('hidden.bs.modal', function() {
                $('#editEventForm')[0].reset();
                document.querySelector('.modal-backdrop')?.remove();
                document.body.classList.remove('modal-open');
            });

            // Handle ESC key to properly close modals
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideModal('#addEventModal');
                    hideModal('#editEventModal');
                }
            });

            function updatePagination(totalItems) {
                const totalPages = Math.ceil(totalItems / itemsPerPage);
                let start = totalItems === 0 ? 0 : ((currentPage - 1) * itemsPerPage) + 1;
                let end = Math.min(currentPage * itemsPerPage, totalItems);
                
                const showing = totalItems === 0 
                    ? 'Showing 0 of 0 entries' 
                    : `Showing ${start}-${end} of ${totalItems} entries`;
                    
                $('#showing-entries').text(showing);

                // Update page numbers
                const pageNumbers = $('.page-numbers');
                pageNumbers.empty();

                if (totalItems === 0) return; // Don't show pagination if no entries

                // First page
                pageNumbers.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="1"><i class="fas fa-angle-double-left"></i></a>
                    </li>
                `);

                // Previous page
                pageNumbers.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}"><i class="fas fa-angle-left"></i></a>
                    </li>
                `);

                // Page numbers
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    pageNumbers.append(`
                        <li class="page-item ${currentPage === i ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                }

                // Next page
                pageNumbers.append(`
                    <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}"><i class="fas fa-angle-right"></i></a>
                    </li>
                `);

                // Last page
                pageNumbers.append(`
                    <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${totalPages}"><i class="fas fa-angle-double-right"></i></a>
                    </li>
                `);

                $('#eventsPagination').parent().parent().toggle(totalPages > 1);
            }

            // Handle pagination clicks
            $(document).on('click', '.page-numbers .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                
                if (page === 'first') {
                    currentPage = 1;
                } else if (page === 'previous') {
                    currentPage = Math.max(1, currentPage - 1);
                } else if (page === 'next') {
                    currentPage = Math.min(totalPages, currentPage + 1);
                } else if (page === 'last') {
                    currentPage = totalPages;
                } else {
                    currentPage = parseInt(page);
                }
                
                loadEvents();
            });

            // Update the modal hidden handlers
            $('#addEventModal, #editEventModal').on('hidden.bs.modal', function() {
                const modalId = `#${this.id}`;
                $(this).find('form')[0].reset();
                
                // Remove backdrop and modal-open class first
                document.querySelector('.modal-backdrop')?.remove();
                document.body.classList.remove('modal-open');
                
                // Then dispose of the modal
                const modal = bootstrap.Modal.getInstance(this);
                if (modal) {
                    modal.dispose();
                }
            });
        });
    }
};

// Initialize the event manager
eventManager.init();