$(document).ready(function () {
  // Try different ways to find the logout button
  const logoutButton = $("#logoutButton").length
    ? $("#logoutButton")
    : $(".logout-button, button:contains('Logout'), a:contains('Logout')");

  if (logoutButton.length === 0) {
    console.warn("Logout button not found on this page!");
  } else {
  }

  // Use event delegation in case button is added dynamically
  $(document).on(
    "click",
    "#logoutButton, .logout-button, button:contains('Logout'), a:contains('Logout')",
    function (e) {
      console.log("Logout button clicked");
      e.preventDefault();

      Swal.fire({
        title: "Logout Confirmation",
        text: "Are you sure you want to log out?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, logout",
        cancelButtonText: "No, cancel",
        width: "400px",
        customClass: {
          container: "small-modal",
          popup: "small-modal",
          header: "small-modal-header",
          title: "small-modal-title",
          content: "small-modal-content",
        },
      }).then((result) => {
        if (result.isConfirmed) {
          console.log("Logout confirmed");

          // If session manager exists, use it for logout
          if (window.sessionManager) {
            console.log("Using session manager for logout");
            window.sessionManager.isLoggingOut = true;
            window.sessionManager.logout();
            return;
          } else {
            console.log(
              "No session manager found, using fallback logout method"
            );
          }

          // Get the correct path to logout_handler.php based on current location
          let basePath;
          if (window.location.pathname.includes("/view/")) {
            basePath = "../../src/backend/logout_handler.php";
          } else {
            basePath = "/src/backend/logout_handler.php";
          }
          console.log("Using logout endpoint:", basePath);

          // Fallback if session manager is not available
          // Tell session manager we're logging out
          if (window.sessionManager) {
            window.sessionManager.isHandlingTimeout = true;
          }

          // Show loading state
          Swal.fire({
            title: "Logging out...",
            html: '<div class="loading-spinner"></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
              Swal.showLoading();
            },
            width: "400px",
            customClass: {
              container: "small-modal",
              popup: "small-modal",
              header: "small-modal-header",
              title: "small-modal-title",
              content: "small-modal-content",
            },
          });

          // Perform logout
          $.ajax({
            url: basePath,
            type: "POST",
            dataType: "json",
            success: function (response) {
              console.log("Logout response:", response);
              if (response.success) {
                // Show success message with Lottie animation
                Swal.fire({
                  title: "Logged Out",
                  html: `
                    <div id="lottie-success" style="width: 200px; height: 200px; margin: 0 auto;"></div>
                    <p style="margin-top: 1rem; font-size: 1.1rem; color: #666;">${response.message || "You have been logged out successfully"}</p>
                  `,
                  showConfirmButton: true,
                  confirmButtonText: "OK",
                  allowOutsideClick: false,
                  width: "450px",
                  customClass: {
                    container: "small-modal",
                    popup: "small-modal",
                    header: "small-modal-header",
                    title: "small-modal-title",
                    content: "small-modal-content",
                  },
                  didOpen: () => {
                    // Load Lottie animation
                    if (typeof lottie !== 'undefined') {
                      lottie.loadAnimation({
                        container: document.getElementById('lottie-success'),
                        renderer: 'svg',
                        loop: false,
                        autoplay: true,
                        path: '../../assets/animations/success-checkmark.json'
                      });
                    }
                  }
                }).then(() => {
                  // Redirect to login page
                  console.log("Redirecting to login page");
                  window.location.href = "/index.php";
                });
              } else {
                console.error("Logout failed:", response.message);
                Swal.fire({
                  title: "Error!",
                  text: response.message || "Failed to logout",
                  icon: "error",
                  width: "400px",
                  customClass: {
                    container: "small-modal",
                    popup: "small-modal",
                    header: "small-modal-header",
                    title: "small-modal-title",
                    content: "small-modal-content",
                  },
                });
                if (window.sessionManager) {
                  window.sessionManager.isHandlingTimeout = false;
                }
              }
            },
            error: function (xhr, status, error) {
              console.error("Logout AJAX error:", error);
              console.error("Status:", status);
              console.error("Response text:", xhr.responseText);

              Swal.fire({
                title: "Error!",
                text: "Failed to logout. Please try again.",
                icon: "error",
                width: "400px",
                customClass: {
                  container: "small-modal",
                  popup: "small-modal",
                  header: "small-modal-header",
                  title: "small-modal-title",
                  content: "small-modal-content",
                },
              });
              if (window.sessionManager) {
                window.sessionManager.isHandlingTimeout = false;
              }
            },
          });
        } else {
          console.log("Logout cancelled by user");
        }
      });
    }
  );
});
