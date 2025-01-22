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
        
        let dataToUse = filteredData || allAppointments;
        
        // Apply search filter if search text exists
        const searchText = $('#appointmentSearch').val().toLowerCase();
        if (searchText) {
            dataToUse = dataToUse.filter(appointment => {
                return (
                    (appointment.user_id || '').toString().toLowerCase().includes(searchText) ||
                    (appointment.date || '').toLowerCase().includes(searchText) ||
                    (appointment.time || '').toLowerCase().includes(searchText) ||
                    (appointment.description || '').toLowerCase().includes(searchText) ||
                    (appointment.status || '').toLowerCase().includes(searchText)
                );
            });
        }
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedData = dataToUse.slice(startIndex, endIndex);
        
        if (paginatedData.length === 0) {
            table.append('<tr><td colspan="6" class="text-center">No matching records found</td></tr>');
        } else {
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
        }

        updatePagination(dataToUse.length);
    }

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

        $('#appointmentsPagination').parent().parent().toggle(totalPages > 1);
    }

    // Initial load
    loadAppointments();

    // Reload every 5 minutes
    setInterval(loadAppointments, 300000);

    // Enhanced search functionality
    let searchTimeout;
    $('#appointmentSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1; // Reset to first page when searching
            updateTable();
        }, 300); // Wait 300ms after user stops typing
    });

    // Items per page change handler
    $('#appointmentsPerPage').on('change', function() {
        itemsPerPage = parseInt($(this).val());
        currentPage = 1;
        loadAppointments();
    });


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
        
        loadAppointments();
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
