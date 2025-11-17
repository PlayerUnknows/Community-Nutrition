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

  loginForm.submit(function (e) {
    e.preventDefault(); // Prevent the default form submission

    let login = $("#email").val();
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

    // Disable button and show loading state
    loginButton
      .prop("disabled", true)
      .html(
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...'
      );

    // Perform AJAX request
    $.ajax({
      url: "../src/controllers/UserController.php?action=login",
      type: "POST",
      data: {
        login: login,
        password: password,
      },
      success: function (response) {
        try {
          // Try to parse the response as JSON
          let jsonResponse = JSON.parse(response);

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
        console.error("Response:", xhr.responseText);

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
});
