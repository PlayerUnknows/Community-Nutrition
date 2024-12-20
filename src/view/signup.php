<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Community Nutrition Information System</title>
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css">
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="signup-container">
            <h1 class="text-center mb-4">Create an Account</h1>
            <p class="text-center text-muted mb-4">Join the Community Nutrition Information System today.</p>

            <form id="signupForm" novalidate>
                <!-- Email -->
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email address" required class="form-control">

                </div>

                <!-- Password -->
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" placeholder="Create a password" required class="form-control">
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
                    <small class="form-text">Choose your role in the system.</small>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                <!-- Back Button -->
                <!-- Add the back button after the form submission 
                    <button id="backButton" class="back-button btn btn-secondary mt-2 w-100">Back</button>
                        -->
            </form>
        </div>
    </div>

    <!-- Include jQuery library -->
    <script src="../../node_modules/jquery/dist/jquery.js" type="text/javascript"></script>

    <!-- Include Popper.js for Bootstrap tooltips -->
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.js" type="text/javascript"></script>

    <!-- Include Bootstrap JavaScript -->
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>

    <!-- Include SweetAlert for alert modals -->
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.js" type="text/javascript"></script>

    <!-- Link to your separate JS file -->
    <script src="/src/script/signup.js" type="text/javascript"></script>
    <script src="/src/script/loader.js" type="text/javascript"></script>
</body>

</html>