<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Community Nutrition Information System</title>
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css">
    <style>
        :root {
            --primary-blue: #3498db;
            --light-blue: #5dade2;
            --dark-gray: #495057;
            --error-red: #dc3545;
            --success-green: #28a745;
            --glow-color: rgba(52, 152, 219, 0.6);
        }

        .signup-form {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-top: 6px solid var(--primary-blue);
            padding: 2rem;
            width: 100%;
            max-width: 450px;
            position: relative;
            animation: pulseGlow 3s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 5px var(--glow-color),
                           0 0 10px var(--glow-color),
                           0 0 15px var(--glow-color);
            }
            50% {
                box-shadow: 0 0 20px var(--glow-color),
                           0 0 35px var(--glow-color),
                           0 0 50px var(--glow-color);
            }
            100% {
                box-shadow: 0 0 5px var(--glow-color),
                           0 0 10px var(--glow-color),
                           0 0 15px var(--glow-color);
            }
        }

        .signup-form h2 {
            color: var(--primary-blue);
            font-size: 1.8rem;
        }

        .signup-form .form-control {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 0.75rem;
            font-size: 0.95rem;
        }

        .signup-form .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.25);
        }

        .signup-form .btn-primary {
            background-color: var(--primary-blue);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .signup-form .btn-primary:hover {
            background-color: #2980b9;
        }

        .form-control.is-invalid {
            border-color: var(--error-red);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-control.is-invalid:focus {
            border-color: var(--error-red);
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }

        .invalid-feedback {
            display: none;
            color: var(--error-red);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }

        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            display: none;
        }

        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffecb5;
            color: #664d03;
        }

        /* Additional styles for signup.js error handling */
        .error {
            border-color: var(--error-red) !important;
        }

        .error-msg {
            color: var(--error-red);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        @media (max-width: 768px) {
            .signup-form {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <form id="signupForm" class="signup-form needs-validation" novalidate>
            <h2 class="text-center mb-4">Create an Account</h2>
            <p class="text-center text-muted mb-4">Join the Community Nutrition Information System today.</p>

            <!-- Alert for form errors -->
            <div class="alert alert-warning" id="formAlert" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Please fill in all required fields properly.
            </div>

            <!-- Email -->
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter your email address" required class="form-control">
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Please enter a valid email address.
                </div>
            </div>

            <!-- Password -->
            <div class="form-group mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" placeholder="Create a password" required class="form-control"
                    pattern=".{8,}" title="Password must be at least 8 characters long">
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Password must be at least 8 characters long.
                </div>
            </div>

            <!-- Role Selection -->
            <div class="form-group mb-3">
                <label for="role" class="form-label">Select Role</label>
                <select name="role" id="role" required class="form-control">
                    <option value="" disabled selected>Select role</option>
                    <option value="1">Parent</option>
                    <option value="2">Health Worker</option>
                    <option value="3">Administrator</option>
                </select>
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Please select a role.
                </div>
                <small class="form-text">Choose your role in the system.</small>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>
    </div>

    <!-- Scripts -->
    <script src="../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="/src/script/signup.js"></script>
    <script src="/src/script/loader.js"></script>

    <script>
        // Form validation
        (function () {
            'use strict'

            const form = document.getElementById('signupForm');
            const alert = document.getElementById('formAlert');

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                
                if (!form.checkValidity()) {
                    event.stopPropagation();
                    alert.style.display = 'block';
                } else {
                    alert.style.display = 'none';
                    
                    const submitButton = $(this).find('button[type="submit"]');
                    submitButton.prop('disabled', true).text('Submitting...');

                    $.ajax({
                        url: '../../src/controllers/UserController.php?action=signup',
                        type: 'POST',
                        data: {
                            email: $('#email').val().trim(),
                            password: $('#password').val().trim(),
                            role: $('#role').val()
                        },
                        success: function (response) {
                            try {
                                response = JSON.parse(response);
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        form.reset();
                                        form.classList.remove('was-validated');
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
                                    if (response.error === "email_exists") {
                                        $('#email').addClass('is-invalid')
                                            .siblings('.invalid-feedback')
                                            .text('This email is already in use.');
                                    }
                                }
                            } catch (e) {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An unexpected error occurred.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function () {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to connect to the server.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        },
                        complete: function() {
                            submitButton.prop('disabled', false).text('Sign Up');
                        }
                    });
                }

                form.classList.add('was-validated');
            }, false);

            // Hide alert when user starts fixing errors
            form.addEventListener('input', function () {
                if (form.checkValidity()) {
                    alert.style.display = 'none';
                }
                // Remove is-invalid class when user starts typing
                $(this).find('.is-invalid').removeClass('is-invalid');
            });
        })();
    </script>
</body>
</html>