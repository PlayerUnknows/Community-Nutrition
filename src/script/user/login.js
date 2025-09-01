$(document).ready(function () {
  const loginForm = $("#loginForm");
  const loginButton = loginForm.find('button[type="submit"]');
  const originalButtonText = loginButton.text();
  const MIN_LOADING_TIME = 1000; // Minimum loading time in milliseconds

  // Clear session reset cookie if it exists
  // This helps prevent issues with session resets
  document.cookie =
    "session_reset_time=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

  // Check for error message in session
  if (getUrlParameter("error")) {
    Swal.fire({
      title: "Error!",
      text: getUrlParameter("error"),
      icon: "error",
      confirmButtonText: "OK",
    });
  }

  // Generate CSRF token for this session
  function generateCSRFToken() {
    return 'csrf_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
  }

  // Hash password using SHA-256 (this is just for transmission security, not storage)
  async function hashPassword(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    return hashHex;
  }

  // Add CSRF token to form (temporarily disabled)
  // const csrfToken = generateCSRFToken();
  // loginForm.append('<input type="hidden" name="csrf_token" value="' + csrfToken + '">');

  loginForm.submit(async function (e) {
    e.preventDefault(); // Prevent the default form submission

    let login = $("#email").val().trim();
    let password = $("#password").val();
    let startTime = Date.now(); // Record start time

    // Validate input
    if (!login || !password) {
      Swal.fire({
        title: "Login Failed!",
        text: "Please enter both email/ID and password.",
        icon: "error",
        confirmButtonText: "OK",
      });
      return;
    }

    // Additional validation
    if (password.length < 6) {
      Swal.fire({
        title: "Login Failed!",
        text: "Password must be at least 6 characters long.",
        icon: "error",
        confirmButtonText: "OK",
      });
      return;
    }

    // Disable button and show loading state
    loginButton
      .prop("disabled", true)
      .html(
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...'
      );

    try {
      // Hash the password before sending (temporarily disabled)
      // const hashedPassword = await hashPassword(password);
      
      // Clear the password field immediately for security
      $("#password").val('');

      // Perform AJAX request with regular password (temporarily)
      $.ajax({
        url: "../src/controllers/UserController.php?action=loginUser",
        type: "POST",
        data: {
          login: login,
          password: password, // Use regular password for now
          // csrf_token: csrfToken,
          // timestamp: Date.now() // Add timestamp for additional security
        },
        success: function (response) {
          try {
            // Response is already a JavaScript object, no need to parse
            let jsonResponse = response;

            // Calculate remaining loading time
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);

            setTimeout(() => {
              if (jsonResponse.success) {
                // Check if user is an admin (role 3)
                if (jsonResponse.role == 3) {
                  // Clear any session cookies that might cause issues
                  document.cookie =
                    "session_reset_time=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

                  // Keep button in loading state during redirect
                  window.location.href = jsonResponse.redirect;
                } else {
                  // Reset button state
                  loginButton.prop("disabled", false).html(originalButtonText);

                  // Show unauthorized message for non-admin users
                  Swal.fire({
                    title: "Access Denied",
                    text: "This system is for administrators only.",
                    icon: "warning",
                    confirmButtonText: "OK",
                  });
                }
              } else {
                // Reset button state
                loginButton.prop("disabled", false).html(originalButtonText);

                // Show error message
                Swal.fire({
                  title: "Login Failed!",
                  text: jsonResponse.message || "Invalid login credentials.",
                  icon: "error",
                  confirmButtonText: "OK",
                });
              }
            }, remainingTime);
          } catch (e) {
            console.error("Error parsing JSON response:", e);

            // Calculate remaining loading time
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);

            setTimeout(() => {
              // Reset button state
              loginButton.prop("disabled", false).html(originalButtonText);

              Swal.fire({
                title: "Error!",
                text: "Invalid server response. Please try again.",
                icon: "error",
                confirmButtonText: "OK",
              });
            }, remainingTime);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", error);
          console.error("Status:", status);
          // Don't log the full response for security reasons
          console.error("Response status:", xhr.status);

          // Calculate remaining loading time
          const elapsedTime = Date.now() - startTime;
          const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);

          setTimeout(() => {
            // Reset button state
            loginButton.prop("disabled", false).html(originalButtonText);

            Swal.fire({
              title: "Error!",
              text: "An unexpected error occurred. Please try again later.",
              icon: "error",
              confirmButtonText: "OK",
            });
          }, remainingTime);
        },
      });
    } catch (error) {
      console.error("Error hashing password:", error);
      loginButton.prop("disabled", false).html(originalButtonText);
      
      Swal.fire({
        title: "Error!",
        text: "Security error occurred. Please try again.",
        icon: "error",
        confirmButtonText: "OK",
      });
    }
  });

  // Helper function to get URL parameters
  function getUrlParameter(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    var results = regex.exec(location.search);
    return results === null
      ? ""
      : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  // Clear password field on page unload for security
  $(window).on('beforeunload', function() {
    $("#password").val('');
  });

  // Clear password field when user switches tabs/windows
  $(window).on('blur', function() {
    setTimeout(function() {
      $("#password").val('');
    }, 1000);
  });
});
