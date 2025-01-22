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
            cancelButtonText: 'No, cancel',
            width: '400px',
            customClass: {
                container: 'small-modal',
                popup: 'small-modal',
                header: 'small-modal-header',
                title: 'small-modal-title',
                content: 'small-modal-content'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tell session manager we're logging out
                if (window.sessionManager) {
                    window.sessionManager.isHandlingTimeout = true;
                }

                // Show loading state
                Swal.fire({
                    title: 'Logging out...',
                    html: '<div class="loading-spinner"></div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    width: '400px',
                    customClass: {
                        container: 'small-modal',
                        popup: 'small-modal',
                        header: 'small-modal-header',
                        title: 'small-modal-title',
                        content: 'small-modal-content'
                    }
                });

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
                                timer: 1500,
                                width: '400px',
                                customClass: {
                                    container: 'small-modal',
                                    popup: 'small-modal',
                                    header: 'small-modal-header',
                                    title: 'small-modal-title',
                                    content: 'small-modal-content'
                                }
                            }).then(() => {
                                // Redirect to login page
                                window.location.href = '../../index.php';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to logout',
                                icon: 'error',
                                width: '400px',
                                customClass: {
                                    container: 'small-modal',
                                    popup: 'small-modal',
                                    header: 'small-modal-header',
                                    title: 'small-modal-title',
                                    content: 'small-modal-content'
                                }
                            });
                            if (window.sessionManager) {
                                window.sessionManager.isHandlingTimeout = false;
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to logout. Please try again.',
                            icon: 'error',
                            width: '400px',
                            customClass: {
                                container: 'small-modal',
                                popup: 'small-modal',
                                header: 'small-modal-header',
                                title: 'small-modal-title',
                                content: 'small-modal-content'
                            }
                        });
                        if (window.sessionManager) {
                            window.sessionManager.isHandlingTimeout = false;
                        }
                    }
                });
            }
        });
    });
});
