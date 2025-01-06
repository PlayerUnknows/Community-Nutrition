$(document).ready(function () {
    $('#logoutButton').click(function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Logout Confirmation',
            text: 'Are you sure you want to log out?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform logout
                $.ajax({
                    url: '../../src/backend/logout_handler.php',
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Show success message
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Redirect to login page
                                window.location.href = '../../index.php';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to logout',
                                icon: 'error'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to logout. Please try again.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});
