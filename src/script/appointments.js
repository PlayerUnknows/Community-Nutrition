$(document).ready(function() {
    // Load appointments using regular AJAX
    function loadAppointments() {
        $.ajax({
            url: "/src/controllers/AppointmentController.php?action=getAppointments",
            type: "GET",
            success: function(data) {
                if (!data) return;
                
                const table = $('#appointmentsTable tbody');
                table.empty(); // Clear existing rows
                
                data.forEach(function(appointment) {
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

    // Initial load
    loadAppointments();

    // Reload every 5 minutes
    setInterval(loadAppointments, 300000);

    // Custom search functionality
    $('#appointmentSearch').on('keyup', function() {
        const searchValue = $(this).val().toLowerCase();
        $('#appointmentsTable tbody tr').each(function() {
            const row = $(this);
            const isVisible = row.find('td').text().toLowerCase().includes(searchValue);
            row.toggle(isVisible);
        });
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
