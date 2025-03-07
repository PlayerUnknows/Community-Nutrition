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

    .was-validated .form-control:invalid~.invalid-feedback,
    .form-control.is-invalid~.invalid-feedback {
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

    .form-check-input:checked {
        background-color: var(--primary-blue);
        border-color: var(--primary-blue);
    }

    .form-check-input:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }

    .form-check-label a {
        color: var(--primary-blue);
        text-decoration: none;
    }

    .form-check-label a:hover {
        text-decoration: underline;
    }
</style>

<div class="container d-flex justify-content-center align-items-center min-vh-60">
    <form id="signupForm" class="signup-form needs-validation" novalidate>
        <h2 class="text-center mb-4">Create an Account</h2>
        <p class="text-center text-muted mb-4">Join the Community Nutrition Information System today.</p>

        <!-- Alert for form errors -->
        <div class="alert alert-warning" id="formAlert" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Please fill in all required fields properly.
        </div>

        <!-- Name Fields -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" name="firstName" id="firstName" placeholder="Enter first name" required class="form-control">
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Please enter your first name.
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="middleName" class="form-label">Middle Name</label>
                <input type="text" name="middleName" id="middleName" placeholder="Enter middle name" required class="form-control">
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Please enter your middle name.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" name="lastName" id="lastName" placeholder="Enter last name" required class="form-control">
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Please enter your last name.
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="suffix" class="form-label">Suffix</label>
                <input type="text" name="suffix" id="suffix" placeholder="Jr., Sr., III" class="form-control">
            </div>
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

        <!-- Data Privacy Agreement -->
        <div class="form-group mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="privacyAgreement" required>
                <label class="form-check-label" for="privacyAgreement">
                    I agree to the processing of my personal information in accordance with the
                    <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Data Privacy Policy</a>
                </label>
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    You must agree to the Data Privacy Policy to continue.
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100">Sign Up</button>
    </form>
</div>

<!-- Credentials Modal -->
<div class="modal" id="credentialsModal" tabindex="-1" role="dialog" aria-labelledby="credentialsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="credentialsModalLabel">Account Credentials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please save these credentials. You will need them to log in.
                </div>
                <div class="credentials-container">
                    <div class="mb-3">
                        <label class="form-label">User ID:</label>
                        <div class="input-group">
                            <input type="text" id="userIdDisplay" class="form-control" readonly>
                            <button class="btn btn-outline-secondary copy-btn" type="button" data-copy="userIdDisplay">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Temporary Password:</label>
                        <div class="input-group">
                            <input type="text" id="tempPasswordDisplay" class="form-control" readonly>
                            <button class="btn btn-outline-secondary copy-btn" type="button"
                                data-copy="tempPasswordDisplay">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Data Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Information We Collect</h6>
                <p>We collect personal information that you voluntarily provide when signing up for the Community
                    Nutrition Information System, including:</p>
                <ul>
                    <li>Full Name</li>
                    <li>Email Address</li>
                    <li>Role in the System</li>
                </ul>

                <h6>How We Use Your Information</h6>
                <p>Your information will be used for:</p>
                <ul>
                    <li>Account creation and management</li>
                    <li>Communication regarding system updates and notifications</li>
                    <li>Providing access to appropriate system features based on your role</li>
                    <li>Improving our services and user experience</li>
                </ul>

                <h6>Data Protection</h6>
                <p>We implement appropriate technical and organizational measures to protect your personal information
                    against unauthorized access, modification, or disclosure.</p>

                <h6>Your Rights</h6>
                <p>Under the Data Privacy Act of 2012 (RA 10173), you have the right to:</p>
                <ul>
                    <li>Access your personal information</li>
                    <li>Correct inaccurate or incomplete data</li>
                    <li>Object to the processing of your personal data</li>
                    <li>Request for the deletion of your personal information</li>
                    <li>Be informed of any changes to this privacy policy</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
<script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

<!-- Initialize Bootstrap components -->
<script>
    // Initialize modals globally
    var credentialsModal;
    var privacyModal;

    document.addEventListener('DOMContentLoaded', function () {
        credentialsModal = new bootstrap.Modal(document.getElementById('credentialsModal'), {
            backdrop: 'static',
            keyboard: false
        });

        privacyModal = new bootstrap.Modal(document.getElementById('privacyModal'), {
            keyboard: true
        });
    });
</script>

<!-- Your custom scripts -->
<script src="/src/script/signup.js"></script>
<script src="/src/script/loader.js"></script>

</body>
</html>