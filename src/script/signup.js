// Load signup form when the Account tab is clicked
document.getElementById('acc-reg').addEventListener('click', function() {
    // Get the container where we'll put the form
    const container = document.getElementById('signupFormContainer');
    
    // Use AJAX to load the signup form
    $.ajax({
        url: '../view/signup.php',
        method: 'GET',
        success: function(response) {
            // Extract the form content from the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(response, 'text/html');
            const signupContainer = doc.querySelector('.signup-container');
            
            if (signupContainer) {
                container.innerHTML = signupContainer.outerHTML;
                
                // Add the submitBtn class to the submit button
                const submitButton = container.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.classList.add('submitBtn');
                }

                // Reinitialize the form submission handler
                $('#signupForm').submit(function (e) {
                    e.preventDefault();

                    const email = $('#email').val().trim();
                    const password = $('#password').val().trim();
                    const role = $('#role').val();

                    // Clear previous error messages
                    clearErrorMessages();

                    // Basic client-side validation
                    if (!validateForm(email, password, role)) {
                        return;
                    }

                    // Disable the submit button during request
                    const submitButton = $('.submitBtn');
                    submitButton.prop('disabled', true).text('Submitting...');

                    // Perform AJAX request
                    $.ajax({
                        url: '../../src/controllers/UserController.php?action=signup',
                        type: 'POST',
                        data: { email, password, role },
                        success: function (response) {
                            console.log('Server response:', response);
                            try {
                                response = JSON.parse(response);

                                if (response.success) {
                                    console.log(Swal);
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        resetForm();
                                        // Refresh tables
                                        if (typeof refreshTable === 'function') {
                                            refreshTable('usersTable');
                                            refreshTable('auditTable');
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });

                                    if (response.error && response.error === "email_exists") {
                                        $('#email').addClass('error')
                                            .after('<span class="error-msg">This email is already in use.</span>');
                                    }
                                }
                            } catch (e) {
                                console.error('Invalid JSON response:', e);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An unexpected error occurred.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to connect to the server.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        },
                        complete: function () {
                            submitButton.prop('disabled', false).text('Sign Up');
                        }
                    });
                });
            }
        },
        error: function(xhr, status, error) {
            container.innerHTML = '<div class="alert alert-danger">Error loading signup form. Please try again.</div>';
            console.error('Error loading signup form:', error);
        }
    });
});

// Helper functions
function validateForm(email, password, role) {
    let isValid = true;

    if (!password || !role || role === "0") {
        isValid = false;
        if (!password) {
            $('#password').addClass('error')
                .after('<span class="error-msg">Password is required.</span>');
        }
        if (!role || role === "0") {
            $('#role').addClass('error')
                .after('<span class="error-msg">Please select a role.</span>');
        }
    }
    return isValid;
}

function clearErrorMessages() {
    $('.error-msg').remove();
    $('.error').removeClass('error');
}

function resetForm() {
    $('#signupForm')[0].reset();
    clearErrorMessages();
}