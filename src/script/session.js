// Session timeout handling
class SessionManager {
  constructor(timeoutWarningMinutes = 5) {
    // 5 minutes warning before timeout
    this.timeoutWarningMinutes = timeoutWarningMinutes;
    this.checkInterval = 30000; // Check every 30 seconds
    this.activityResetThreshold = 60000; // Only reset session after 1 minute of inactivity
    this.isActive = true;
    this.lastActivityTime = Date.now();
    this.lastResetTime = Date.now(); // Track when we last reset the session
    this.warningShown = false;
    this.isHandlingTimeout = false;
    this.isLoggingOut = false;
    this.sessionTimeout = 1800; // 30 minutes in seconds
    this.setupSessionMonitoring();
    this.setupActivityListeners();
  }

  showWarning() {
    if (!this.warningShown && !this.isHandlingTimeout) {
      this.warningShown = true;
      Swal.fire({
        title: "Session Timeout Warning",
        html: "Your session will expire in 5 minutes.<br>Would you like to stay logged in?",
        icon: "warning",
        timer: 300000, // 5 minutes
        timerProgressBar: true,
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        allowOutsideClick: false,
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
          this.resetTimer(true); // Force reset
          Swal.fire({
            title: "Extended!",
            text: "Session extended successfully",
            icon: "success",
            timer: 1500,
            showConfirmButton: false,
            width: "400px",
            customClass: {
              container: "small-modal",
              popup: "small-modal",
              header: "small-modal-header",
              title: "small-modal-title",
              content: "small-modal-content",
            },
          });
        } else if (
          result.dismiss === Swal.DismissReason.cancel ||
          result.dismiss === Swal.DismissReason.timer
        ) {
          this.handleSessionTimeout();
        }
        this.warningShown = false;
      });
    }
  }

  hideWarning() {
    if (this.warningShown) {
      Swal.close();
      this.warningShown = false;
    }
  }

  setupSessionMonitoring() {
    // Check session status periodically
    setInterval(() => this.checkSession(), this.checkInterval);

    // Separate interval to check if activity requires a timer reset
    setInterval(() => this.checkActivity(), this.activityResetThreshold);
  }

  setupActivityListeners() {
    // Track user activity without immediately resetting the timer
    const events = [
      "mousedown",
      "mousemove",
      "keydown",
      "scroll",
      "touchstart",
      "click",
    ];

    // Use a throttled approach to track activity
    let isThrottled = false;
    const throttleDelay = 2000; // Only register activity every 2 seconds at most

    const throttledActivityHandler = () => {
      if (!isThrottled && !this.isHandlingTimeout && !this.isLoggingOut) {
        this.isActive = true;
        this.lastActivityTime = Date.now();

        // Hide warning if visible and user becomes active
        if (this.warningShown) {
          this.hideWarning();
          this.resetTimer(true); // Force reset when dismissing warning
        }

        isThrottled = true;
        setTimeout(() => {
          isThrottled = false;
        }, throttleDelay);
      }
    };

    events.forEach((event) => {
      document.addEventListener(event, throttledActivityHandler);
    });

  }

  // New method to check if activity warrants a timer reset
  checkActivity() {
    if (this.isHandlingTimeout || this.isLoggingOut) return;

    const now = Date.now();
    const timeSinceLastActivity = now - this.lastActivityTime;
    const timeSinceLastReset = now - this.lastResetTime;

    // If user has been active since last reset, and it's been at least 1 minute since last reset
    if (
      this.isActive &&
      timeSinceLastActivity < this.activityResetThreshold &&
      timeSinceLastReset >= this.activityResetThreshold
    ) {
      console.log("Activity detected, resetting session timer");
      this.resetTimer();
    }
  }

  // Helper method to get the correct path to backend files
  getBackendPath(endpoint) {
    let basePath = "../backend/";

    // If we're in a nested view folder, adjust the path
    if (window.location.pathname.includes("/view/")) {
      if (window.location.pathname.split("/").length > 4) {
        // We're in a deeper folder structure
        basePath = "../../src/backend/";
      }
    } else if (
      window.location.pathname === "/" ||
      window.location.pathname.includes("/index.php")
    ) {
      // We're at the root or index page
      basePath = "src/backend/";
    }

    return basePath + endpoint;
  }

  async checkSession() {
    if (this.isHandlingTimeout || this.isLoggingOut) return;

    try {
      const endpoint = this.getBackendPath(
        "session_handler.php?check_session=1"
      );
  
      const response = await fetch(endpoint);
      const data = await response.json();
  
      if (!data.valid && !this.isHandlingTimeout) {
        this.handleSessionTimeout();
      } else if (data.last_activity) {
        // Calculate time until session expires
        const currentTime = Math.floor(Date.now() / 1000);
        const lastActivity = parseInt(data.last_activity);
        const timeUntilExpire =
          this.sessionTimeout - (currentTime - lastActivity);

        // Show warning if within warning period (5 minutes)
        if (
          timeUntilExpire <= 300 &&
          !this.warningShown &&
          !this.isHandlingTimeout
        ) {
          this.showWarning();
        }

        console.log(
          `Time until session expires: ${Math.floor(
            timeUntilExpire / 60
          )} minutes and ${timeUntilExpire % 60} seconds`
        );
      }
    } catch (error) {
      console.error("Session check failed:", error);
    }
  }

  async handleSessionTimeout() {
    if (this.isHandlingTimeout) return;

    try {
      this.isHandlingTimeout = true;
      console.log("Session timeout detected, destroying session...");
      this.hideWarning();

      const endpoint = this.getBackendPath(
        "session_handler.php?destroy_session=1"
      );
      const response = await fetch(endpoint);
      const data = await response.json();
      console.log("Session destroy response:", data);

      await Swal.fire({
        title: "Session Expired",
        text: "Please log in again",
        icon: "info",
        allowOutsideClick: false,
        confirmButtonText: "OK",
        width: "400px",
        customClass: {
          container: "small-modal",
          popup: "small-modal",
          header: "small-modal-header",
          title: "small-modal-title",
          content: "small-modal-content",
        },
      });

      window.location.href = "/index.php";
    } catch (error) {
      console.error("Session destruction failed:", error);
      window.location.href = "/index.php";
    }
  }

  async resetTimer(forceReset = false) {
    if (
      (!this.isActive && !forceReset) ||
      this.isHandlingTimeout ||
      this.isLoggingOut
    )
      return;

    // Don't reset if it's been less than 1 minute since last reset (unless forced)
    const now = Date.now();
    if (!forceReset && now - this.lastResetTime < this.activityResetThreshold) {
      console.log("Skipping session reset - last reset was too recent");
      return;
    }

    try {
      console.log("Resetting session timer...");
      const endpoint = this.getBackendPath(
        "session_handler.php?reset_session=1"
      );
      const response = await fetch(endpoint);
      const data = await response.json();
      console.log("Session reset response:", data);

      // Update last reset time
      this.lastResetTime = now;

      if (!data.success) {
        console.warn("Session reset failed:", data.message);
        if (!data.session_data || !data.session_data.user_id) {
          this.handleSessionTimeout();
        }
      }
    } catch (error) {
      console.error("Session reset failed:", error);
    }
  }

  async logout() {
    try {
      this.isLoggingOut = true;
      console.log("Initiating logout process...");

      // Determine the correct path based on the current page location
      let logoutUrl;

      if (window.location.pathname.includes("/view/")) {
        // If we're in the /view/ directory structure
        logoutUrl = "../../src/backend/logout_handler.php";
      } else if (window.location.pathname.includes("/src/")) {
        // If we're in another src subdirectory
        logoutUrl = "../backend/logout_handler.php";
      } else {
        // Default path (for root or unknown locations)
        logoutUrl = "/src/backend/logout_handler.php";
      }

      console.log("Current path:", window.location.pathname);
      console.log("Using logout URL:", logoutUrl);

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

      const response = await fetch(logoutUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include", // Include cookies to ensure session is sent
      });

      const data = await response.json();
      console.log("Logout response:", data);

      await Swal.fire({
        html: `
          <div id="lottie-success" style="width: 200px; height: 200px; margin: 0 auto;"></div>
          <p style="margin-top: 1rem; font-size: 1.1rem; color: #666;">You have been logged out successfully</p>
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
          const animation = lottie.loadAnimation({
            container: document.getElementById('lottie-success'),
            renderer: 'svg',
            loop: false,
            autoplay: true,
            path: '../../assets/animations/Success animation.json' // Local animation file
          });
        }
      });

      // Redirect to the login page
      window.location.href = "/index.php";
    } catch (error) {
      console.error("Logout failed:", error);

      // Show error message to user
      Swal.fire({
        title: "Logout Failed",
        text: "There was a problem logging you out: " + error.message,
        icon: "error",
        allowOutsideClick: false,
        confirmButtonText: "OK",
        width: "400px",
        customClass: {
          container: "small-modal",
          popup: "small-modal",
          header: "small-modal-header",
          title: "small-modal-title",
          content: "small-modal-content",
        },
      });

      // Reset the logout flag to allow retry
      this.isLoggingOut = false;
    }
  }
}

// Initialize session manager when document is ready
document.addEventListener("DOMContentLoaded", () => {
  window.sessionManager = new SessionManager();
});
