/**
 * Main application JavaScript file
 * This file handles common functionality across all authenticated pages
 */

// Wait for document to be ready
document.addEventListener("DOMContentLoaded", () => {
  console.log("App.js initialized");

  // Initialize session manager if not on login page
  if (!window.location.pathname.includes("index.php")) {
    // Check if session manager script is loaded
    if (typeof SessionManager === "undefined") {
      console.warn("Session manager not found. Loading dynamically.");

      // Dynamically load session.js if not already loaded
      const sessionScript = document.createElement("script");
      sessionScript.src = "/src/script/session.js";
      sessionScript.onload = () => {
        console.log("Session manager loaded dynamically");
        window.sessionManager = new SessionManager();
      };
      document.head.appendChild(sessionScript);
    } else if (!window.sessionManager) {
      console.log("Initializing session manager");
      window.sessionManager = new SessionManager();
    }
  }

  // Setup global ajax error handling
  if (typeof $ !== "undefined") {
    $(document).ajaxError((event, jqXHR, settings, error) => {
      console.error("AJAX Error:", error, "URL:", settings.url);

      // If unauthorized (session expired)
      if (jqXHR.status === 401) {
        Swal.fire({
          title: "Session Expired",
          text: "Your session has expired. Please log in again.",
          icon: "warning",
          confirmButtonText: "Log In",
        }).then(() => {
          window.location.href = "/index.php";
        });
      }
    });
  }
});
