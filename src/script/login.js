$(document).ready(function () {
    $('#loginForm').submit(function (e) {
        e.preventDefault(); // Prevent the default form submission

        let login = $('#email').val();
        let password = $('#password').val();

        // Perform AJAX request
        $.ajax({
            url: '../src/controllers/UserController.php?action=login', // URL where the login is processed
            type: 'POST',
            data: {
                login: login,
                password: password
            },
            success: function (response) {
                console.log(response);
                try {
                    response = JSON.parse(response); // Parse the JSON response
                } catch (e) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Invalid server response.',
                        icon: 'error'
                    });
                    return;
                }

                if (response.success) {
                    // Show success message without "OK" button and auto-close
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        showConfirmButton: false, // Remove "OK" button
                        timer: 1500 // Auto-close after 1.5 seconds
                    });

                    // Redirect to the provided URL after 1.5 seconds
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500); // Match the timer duration
                } else {
                    // Show error message
                    Swal.fire({
                        title: 'Login Failed!',
                        text: response.message || 'Invalid login credentials.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred. Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                console.error('Error:', error);
            }
        });
    });
});
