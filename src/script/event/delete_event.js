$(document).ready(function() {

    $('#eventTable').on('click', '.delete-event', function() {
        const eventId = $(this).data('id');
        const deleteButton = $(this);
        const originalHtml = deleteButton.html();
        
        // Define Toast at the beginning so it's available in all callbacks
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash me-1"></i>Yes, delete it!',
            cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show only spinner in button
                deleteButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                deleteButton.prop('disabled', true);
                                                                      
                $.ajax({
                    url: '../controllers/EventController.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'deleteEvent',
                        event_prikey: eventId
                    },
                    success: function(response) {
                        if (response && response.success === true) {
                            // Reload table immediately
                            window.eventTable.ajax.reload();
                            
                            // Show toast notification
                            Toast.fire({
                                icon: 'success',
                                title: 'Successfully deleted!'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Failed to delete event'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Delete error:', {xhr, status, error}); // Debug log
                        Toast.fire({
                            icon: 'error',
                            title: 'Failed to connect to the server'
                        });
                    },
                    complete: function() {
                        // Restore button state
                        deleteButton.html(originalHtml);
                        deleteButton.prop('disabled', false);
                    }
                });
            }
        });
    });

});