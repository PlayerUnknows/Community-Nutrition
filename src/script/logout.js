$(document).ready(function () {
    $('#logoutButton').click(function (e) {
        e.preventDefault();

        let timerInterval;
        Swal.fire({
            title: 'Logging out...',
            html: '',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            customClass: {
                title: 'text-sm',
                timerProgressBar: 'bg-primary'
            },
            didOpen: () => {
                const b = Swal.getHtmlContainer().querySelector('b');
                timerInterval = setInterval(() => {
                    if (b) b.textContent = `${Swal.getTimerLeft()}`;
                }, 100);
                // Add custom styles
                const progressBar = Swal.getPopup().querySelector('.swal2-timer-progress-bar');
                if (progressBar) {
                    progressBar.style.backgroundColor = '#007bff';
                }
                // Make title smaller
                const title = Swal.getPopup().querySelector('.swal2-title');
                if (title) {
                    title.style.fontSize = '1rem';
                }
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                // Perform logout
                $.ajax({
                    url: '/src/backend/logout_handler.php',
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
                                window.location.href = response.redirect || '/index.php';
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
                        console.error('Logout error:', error);
                    }
                });
            }
        });
    });
});
