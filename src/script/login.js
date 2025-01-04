$(document).ready(function () {
    const loginForm = $('#loginForm');
    const loginButton = loginForm.find('button[type="submit"]');
    const originalButtonText = loginButton.text();
    const MIN_LOADING_TIME = 1000; // Minimum loading time in milliseconds

    loginForm.submit(function (e) {
        e.preventDefault(); // Prevent the default form submission

        let login = $('#email').val();
        let password = $('#password').val();
        let startTime = Date.now(); // Record start time

        // Disable button and show loading state
        loginButton.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...');

        // Perform AJAX request
        $.ajax({
            url: '../src/controllers/UserController.php?action=login',
            type: 'POST',
            data: {
                login: login,
                password: password
            },
            success: function (response) {
                try {
                    response = JSON.parse(response);
                    
                    // Calculate remaining loading time
                    const elapsedTime = Date.now() - startTime;
                    const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);
                    
                    setTimeout(() => {
                        if (response.success) {
                            // Keep button in loading state during redirect
                            window.location.href = response.redirect;
                        } else {
                            // Reset button state
                            loginButton.prop('disabled', false).html(originalButtonText);
                            
                            // Show error message
                            Swal.fire({
                                title: 'Login Failed!',
                                text: response.message || 'Invalid login credentials.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    }, remainingTime);
                } catch (e) {
                    // Calculate remaining loading time
                    const elapsedTime = Date.now() - startTime;
                    const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);
                    
                    setTimeout(() => {
                        // Reset button state
                        loginButton.prop('disabled', false).html(originalButtonText);
                        
                        Swal.fire({
                            title: 'Error!',
                            text: 'Invalid server response.',
                            icon: 'error'
                        });
                    }, remainingTime);
                }
            },
            error: function (xhr, status, error) {
                // Calculate remaining loading time
                const elapsedTime = Date.now() - startTime;
                const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);
                
                setTimeout(() => {
                    // Reset button state
                    loginButton.prop('disabled', false).html(originalButtonText);
                    
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again later.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    console.error('Error:', error);
                }, remainingTime);
            }
        });
    });
});
