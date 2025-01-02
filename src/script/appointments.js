$(document).ready(function() {
    let currentPage = 1;
    let itemsPerPage = parseInt($('#appointmentsPerPage').val()) || 10;
    let totalPages = 0;
    let allAppointments = [];

    function loadAppointments() {
        $.ajax({
            url: "/src/controllers/AppointmentController.php?action=getAppointments",
            type: "POST",
            data: {
                page: currentPage,
                length: itemsPerPage,
                search: $('#appointmentSearch').val()
            },
            success: function(data) {
                if (!data) return;
                
                allAppointments = data;
                updateTable();
            },
            error: function(xhr, status, error) {
                console.error("Error loading appointments:", error);
                Swal.fire(
                    'Error!',
                    'Failed to load appointments.',
                    'error'
                );
            }
        });
    }

    function updateTable(filteredData = null) {
        const table = $('#appointmentsTable tbody');
        table.empty();
        
        const dataToUse = filteredData || allAppointments;
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedData = dataToUse.slice(startIndex, endIndex);
        
        paginatedData.forEach(function(appointment) {
            const isCancelled = appointment.status === 'cancelled';
            const row = `<tr class="${isCancelled ? 'text-muted' : ''}">
                <td>${appointment.user_id || ''}</td>
                <td>${moment(appointment.date).format('YYYY-MM-DD') || ''}</td>
                <td>${appointment.time || ''}</td>
                <td>${appointment.description || ''}</td>
                <td>
                    <button class="btn btn-sm btn-primary edit-btn" data-id="${appointment.appointment_prikey}" ${isCancelled ? 'disabled' : ''}>
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm ${isCancelled ? 'btn-secondary' : 'btn-warning'} cancel-btn" 
                            data-id="${appointment.appointment_prikey}"
                            ${isCancelled ? 'disabled' : ''}>
                        <i class="fas ${isCancelled ? 'fa-ban' : 'fa-times'}"></i> 
                        ${isCancelled ? 'Cancelled' : 'Cancel'}
                    </button>
                </td>
                <td>
                    <span class="badge ${isCancelled ? 'bg-secondary' : 'bg-success'}">${isCancelled ? 'Cancelled' : 'Active'}</span>
                </td>
            </tr>`;
            table.append(row);
        });

        updatePagination(dataToUse.length);
    }

    function updatePagination(totalItems) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const showing = `Showing ${((currentPage - 1) * itemsPerPage) + 1}-${Math.min(currentPage * itemsPerPage, totalItems)} of ${totalItems} entries`;
        $('#showing-entries').text(showing);

        // Update page numbers
        const pageNumbers = $('.page-numbers');
        pageNumbers.empty();

        // Calculate range of page numbers to show
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        // Adjust start if we're near the end
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        // Add first page if not in range
        if (startPage > 1) {
            pageNumbers.append(`
                <a class="page-link" href="#" data-page="1">1</a>
                ${startPage > 2 ? '<span class="page-link">...</span>' : ''}
            `);
        }

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            pageNumbers.append(`
                <a class="page-link ${i === currentPage ? 'active' : ''}" 
                   href="#" 
                   data-page="${i}">${i}</a>
            `);
        }

        // Add last page if not in range
        if (endPage < totalPages) {
            pageNumbers.append(`
                ${endPage < totalPages - 1 ? '<span class="page-link">...</span>' : ''}
                <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
            `);
        }

        $('#prevPage').parent().toggleClass('disabled', currentPage === 1);
        $('#nextPage').parent().toggleClass('disabled', currentPage >= totalPages);
        $('#appointmentsPagination').parent().parent().toggle(totalPages > 1);
    }

    // Initial load
    loadAppointments();

    // Reload every 5 minutes
    setInterval(loadAppointments, 300000);

    // Enhanced search functionality
    let searchTimeout;
    $('#appointmentSearch').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadAppointments();
        }, 500);
    });

    // Items per page change handler
    $('#appointmentsPerPage').on('change', function() {
        itemsPerPage = parseInt($(this).val());
        currentPage = 1;
        loadAppointments();
    });

    // Add pagination handlers
    $('#prevPage').on('click', function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            loadAppointments();
        }
    });

    $('#nextPage').on('click', function(e) {
        e.preventDefault();
        const totalPages = Math.ceil(allAppointments.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            loadAppointments();
        }
    });

    // Add page number click handler
    $(document).on('click', '.page-numbers .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && page !== currentPage) {
            currentPage = page;
            loadAppointments();
        }
    });

    // Handle appointment cancellation
    $(document).on('click', '.cancel-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will cancel the appointment. You can't undo this action.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/src/controllers/AppointmentController.php?action=cancel",
                    type: "POST",
                    data: { id: id },
                    success: function(response) {
                        Swal.fire(
                            'Cancelled!',
                            'The appointment has been cancelled.',
                            'success'
                        );
                        loadAppointments(); // Reload the table
                    },
                    error: function(xhr, status, error) {
                        Swal.fire(
                            'Error!',
                            'Failed to cancel appointment.',
                            'error'
                        );
                        console.error("Error cancelling appointment:", error);
                    }
                });
            }
        });
    });

    // Handle appointment editing
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        // Get appointment details
        $.ajax({
            url: "/src/controllers/AppointmentController.php?action=getAppointment",
            type: "GET",
            data: { id: id },
            success: function(appointment) {
                // Populate the edit modal with appointment data
                $('#editAppointmentModal').modal('show');
                $('#edit_appointment_id').val(appointment.appointment_prikey);
                $('#edit_user_id').val(appointment.user_id);
                $('#edit_date').val(moment(appointment.date).format('YYYY-MM-DD'));
                $('#edit_time').val(appointment.time);
                $('#edit_description').val(appointment.description);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching appointment details:", error);
                Swal.fire(
                    'Error!',
                    'Failed to fetch appointment details.',
                    'error'
                );
            }
        });
    });

    // Handle form submission for editing
    $('#editAppointmentForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            id: $('#edit_appointment_id').val(),
            user_id: $('#edit_user_id').val(),
            date: $('#edit_date').val(),
            time: $('#edit_time').val(),
            description: $('#edit_description').val()
        };
        
        $.ajax({
            url: "/src/controllers/AppointmentController.php?action=update",
            type: "POST",
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                $('#editAppointmentModal').modal('hide');
                Swal.fire(
                    'Updated!',
                    'Appointment has been updated.',
                    'success'
                );
                loadAppointments(); // Reload the table
            },
            error: function(xhr, status, error) {
                Swal.fire(
                    'Error!',
                    'Failed to update appointment.',
                    'error'
                );
                console.error("Error updating appointment:", error);
            }
        });
    });
});
