var appointmentManager = {
    init: function() {
        // First destroy any existing DataTable instance
        if ($.fn.DataTable.isDataTable('#appointmentsTable')) {
            $('#appointmentsTable').DataTable().destroy();
        }

        // Clear the table body
        $('#appointmentsTable tbody').empty();

        const table = $('#appointmentsTable').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            ajax: {
                url: "/src/controllers/AppointmentController.php",
                type: 'POST',
                data: { action: 'getAll' },
                dataSrc: function(response) {
                    if (!response || !response.data) {
                        console.error('Invalid response format:', response);
                        return [];
                    }
                    return response.data;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables error:', error);
                    console.error('Server response:', xhr.responseText);
                    return [];
                }
            },
            columns: [
                { data: 'user_id', defaultContent: '' },
                { data: 'date', defaultContent: '' },
                { data: 'time', defaultContent: '' },
                { data: 'description', defaultContent: '' },
                { 
                    data: null,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (!row || !row.appointment_prikey) return '';
                        const appointmentId = row.appointment_prikey;
                        const isCancelled = row.status === 'cancelled';
                        return `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary edit-btn" data-id="${appointmentId}" ${isCancelled ? 'disabled' : ''}>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm ${isCancelled ? 'btn-secondary' : 'btn-warning'} cancel-btn" 
                                        data-id="${appointmentId}"
                                        ${isCancelled ? 'disabled' : ''}>
                                    <i class="fas ${isCancelled ? 'fa-ban' : 'fa-times'}"></i> 
                                    ${isCancelled ? 'Cancelled' : 'Cancel'}
                                </button>
                            </div>
                        `;
                    }
                },
                { data: 'status', defaultContent: '' }
            ],
            order: [[1, 'desc']], // Order by date column descending
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            language: {
                emptyTable: "No appointments found",
                zeroRecords: "No matching appointments found",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)"
            }
        });

        // Handle search input with sanitization
        $('#appointmentSearch').on('keyup', function(e) {
            e.preventDefault();
            const searchValue = $(this).val();
            // Use a timeout to prevent too many searches while typing
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                table.search(searchValue).draw();
            }, 300);
        });

        // Handle length change
        $('#appointmentsPerPage').on('change', function() {
            table.page.len($(this).val()).draw();
        });

        // Handle pagination clicks
        $('#appointmentsPagination').on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            
            if (page === 'prev') {
                table.page('previous').draw('page');
            } else if (page === 'next') {
                table.page('next').draw('page');
            } else {
                table.page(parseInt(page)).draw('page');
            }
        });

        // Handle edit button clicks
        $('#appointmentsTable').on('click', '.edit-btn', function() {
            const appointmentId = $(this).data('id');
            // Get appointment details
            $.ajax({
                url: "/src/controllers/AppointmentController.php?action=getAppointment",
                type: "GET",
                data: { id: appointmentId },
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

        // Handle delete button clicks
        $('#appointmentsTable').on('click', '.cancel-btn', function() {
            const appointmentId = $(this).data('id');
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
                        data: { id: appointmentId },
                        success: function(response) {
                            Swal.fire(
                                'Cancelled!',
                                'The appointment has been cancelled.',
                                'success'
                            );
                            table.ajax.reload();
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
    }
};

// Wait for document ready and initialize
$(document).ready(function() {
    try {
        appointmentManager.init();
    } catch (error) {
        console.error('Error initializing appointment manager:', error);
    }
});
