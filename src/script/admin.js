// Admin page initialization
(function () {
  // Global table references
  const tableInstances = new Map();

  // Wait for dependencies
  const waitForDependencies = (callback) => {
    if (
      typeof jQuery === "undefined" ||
      typeof $.fn === "undefined" ||
      typeof $.fn.DataTable === "undefined"
    ) {
      setTimeout(() => waitForDependencies(callback), 100);
      return;
    }
    callback(jQuery);
  };

  // Check if table is already initialized
  const isTableInitialized = (tableId) => {
    return $.fn.DataTable.isDataTable(tableId) || tableInstances.has(tableId);
  };

  // Initialize a single table
  const initializeTable = ($, tableId, options = {}) => {
    try {
      const table = $(tableId);
      if (!table.length) return null;

      // Skip if already initialized
      if (isTableInitialized(tableId)) {
        return $(tableId).DataTable();
      }

      // Default options
      const defaultOptions = {
        responsive: true,
        pageLength: 10,
        lengthMenu: [
          [5, 10, 25, 50, -1],
          [5, 10, 25, 50, "All"],
        ],
      };

      // Create new instance
      const instance = table.DataTable({
        ...defaultOptions,
        ...options,
      });

      // Store instance reference
      tableInstances.set(tableId, instance);
      return instance;
    } catch (err) {
      console.warn(`Error initializing table ${tableId}:`, err);
      return null;
    }
  };

  // Initialize tables
  const initializeTables = ($) => {
    // No tables should be initialized here
    return;
  };

  // Only adjust tables that exist and are relevant to the current tab
  const adjustTables = (currentTarget) => {
    // Only adjust monitoring table if we're switching to the schedule tab
    // and if MonitoringModule exists and has the adjustTable function
    if (
      currentTarget === "#schedule" &&
      window.MonitoringModule &&
      typeof window.MonitoringModule.adjustTable === "function"
    ) {
      MonitoringModule.adjustTable();
    }
  };

  // Initialize event handlers
  const initializeEventHandlers = ($) => {
    // Handle tab changes
    $('button[data-bs-toggle="tab"]')
      .off("shown.bs.tab")
      .on("shown.bs.tab", function (e) {
        const target = $(e.target).attr("data-bs-target");

        // Only proceed with table adjustments for relevant tabs
        if (target === "#schedule") {
          setTimeout(() => {
            $("#schedule .sub-content").hide();
            $("#monitoring-records").show();
            adjustTables(target);
          }, 200);
        }

        // Ensure any open modals are properly hidden before switching tabs
        $(".modal").modal("hide");
      });

    // Ensure modals are properly disposed when hidden
    $(".modal").on("hidden.bs.modal", function () {
      $(this).data("bs.modal", null);
    });

    // Handle sub-navigation for Nutrition Monitoring
    $("#monitoring-container .sub-nav-button")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        const target = $(this).data("target");
        console.log("Sub-nav clicked:", target); // Debug

        // Hide any open modals before showing new content
        $(".modal").modal("hide");

        // Hide all sub-content first
        $("#schedule .sub-content").hide();
        
        // Show the clicked content
        $(`#${target}`).show();
        
        // If it's the overall report, initialize it
        if (target === "overall-report" && typeof window.generateOPTPlusReport === "function") {
          try {
            console.log("Initializing overall report"); // Debug
            window.generateOPTPlusReport();
          } catch (error) {
            console.error("Error initializing overall report:", error);
          }
        }
        
        // If it's BMI statistics, trigger a custom event to reinitialize handlers
        if (target === "bmi-statistics") {
          setTimeout(() => {
            console.log("BMI statistics shown, triggering reinitialization");
            $(document).trigger('bmi-statistics-shown');
          }, 100);
        }
      });

    // Direct access to Overall Report via URL hash
    if (window.location.hash === "#overall-report") {
      setTimeout(() => {
        // First activate the schedule tab
        $('#schedule-tab').tab('show');
        
        // Then show the overall report content
        $("#schedule .sub-content").hide();
        $("#overall-report").show();
        
        // Initialize the report if the function exists
        if (typeof window.generateOPTPlusReport === "function") {
          window.generateOPTPlusReport();
        }
      }, 500);
    }

    // Show monitoring records by default
    $("#schedule-tab")
      .off("click shown.bs.tab")
      .on("click shown.bs.tab", function () {
        // Hide any open modals
        $(".modal").modal("hide");
        $("#schedule .sub-content").hide();
        $("#monitoring-records").show();
      });

    // Account Registration handling
    $("#acc-reg")
      .off("click shown.bs.tab")
      .on("click shown.bs.tab", function () {
        // Hide any open modals
        $(".modal").modal("hide");
        $(".sub-content").hide();
        $("#signupFormContainer").show();
      });

    // Initialize with signup form visible if account tab is active
    if ($("#acc-reg").hasClass("active")) {
      $(".sub-content").hide();
      $("#signupFormContainer").show();
    }

    // Sub-nav hover effects
    $("#monitoring-container")
      .off("mouseenter mouseleave")
      .hover(
        function () {
          $(this).find(".sub-nav").show();
        },
        function () {
          $(this).find(".sub-nav").hide();
        }
      );

    // Account Registration sub-navigation
    $("#acc-reg-container .sub-nav-button")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        const target = $(this).data("target");

        // Hide any open modals
        $(".modal").modal("hide");

        $(".sub-content").hide();
        $(`#${target}`).show();

        if (target === "view-users" && typeof loadUsers === "function") {
          loadUsers();
        }
      });

    // Initialize toast
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
      },
    });

    // Profile Settings Handler
    $("#profileSettingsBtn")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent event from bubbling up

        // Close the dropdown first
        $(".dropdown-menu").removeClass("show");

        // Show the modal
        Swal.fire({
          title: "Profile Settings",
          html: `
                    <form id="profileSettingsForm" autocomplete="off"class="text-start">
                        <div class="mb-3">
                            <label for="currentEmail" class="form-label">Current Email</label>
                            <input type="email" class="form-control" id="currentEmail" value="${$(
                              "#username"
                            ).text()}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="newEmail" class="form-label">New Email</label>
                            <input type="email" class="form-control" id="newEmail" name="no_autofill_email" autocomplete="off" value="" >
                        </div>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="currentPassword" name="current_pass_fake" autocomplete="new-password" value="">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="currentPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="newPassword" name="new_pass_fake" autocomplete="new-password" value="">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="newPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Leave blank if you don't want to change password or New Email</small>
                        </div>
                    </form>
                `,
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-save me-1"></i>Save Changes',
          cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
          width: "500px",
          customClass: {
            container: "profile-settings-modal",
            popup: "profile-settings-modal",
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-secondary",
          },
          didOpen: () => {
            $('#newEmail, #currentPassword, #newPassword')
            .val('')
            .attr('readonly', true);
        
          setTimeout(() => {
            $('#newEmail, #currentPassword, #newPassword').removeAttr('readonly');
          }, 100);
            // Add password toggle functionality
            $(".toggle-password").on("click", function () {
              const targetId = $(this).data("target");
              const input = $(`#${targetId}`);
              const icon = $(this).find("i");

              if (input.attr("type") === "password") {
                input.attr("type", "text");
                icon.removeClass("fa-eye").addClass("fa-eye-slash");
              } else {
                input.attr("type", "password");
                icon.removeClass("fa-eye-slash").addClass("fa-eye");
              }
            });
          },
          preConfirm: () => {
            const newEmail = $("#newEmail").val().trim();
            const currentPassword = $("#currentPassword").val();
            const newPassword = $("#newPassword").val();

            // Validate inputs
            if (newEmail && !isValidEmail(newEmail)) {
              Swal.showValidationMessage("Please enter a valid email address");
              return false;
            }

            if (!currentPassword) {
              Swal.showValidationMessage("Current password is required");
              return false;
            }

            if (newPassword && newPassword.length < 8) {
              Swal.showValidationMessage(
                "New password must be at least 8 characters long"
              );
              return false;
            }

            return { newEmail, currentPassword, newPassword };
          },
        }).then((result) => {
          if (result.isConfirmed) {
            // Update confirm button to loading state
            const confirmButton = Swal.getConfirmButton();
            const originalText = confirmButton.innerHTML;
            confirmButton.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Updating...
                    `;
            confirmButton.disabled = true;

            // Show processing toast
            Toast.fire({
              icon: "info",
              title: "Processing your request...",
            });

            // Add delay for minimum loading time
            setTimeout(() => {
              // Make AJAX call to update profile
              $.ajax({
                url: "../../src/controllers/UserController.php?action=updateProfile",
                type: "POST",
                data: {
                  newEmail: result.value.newEmail,
                  currentPassword: result.value.currentPassword,
                  newPassword: result.value.newPassword,
                },
                success: function (response) {
                  try {
                    const data = JSON.parse(response);
                    if (data.success) {
                      // Close the modal first
                      Swal.close();

                      // Then show success toast
                      Toast.fire({
                        icon: "success",
                        title: "Profile updated successfully!",
                      }).then(() => {
                        // If email or password was changed, reload after toast
                        if (result.value.newEmail || result.value.newPassword) {
                          Toast.fire({
                            icon: "info",
                            title: "Refreshing page...",
                          }).then(() => {
                            window.location.reload();
                          });
                        }
                      });
                    } else {
                      // Show error toast
                      Toast.fire({
                        icon: "error",
                        title: data.message || "Failed to update profile",
                      });
                      // Restore button state
                      confirmButton.innerHTML = originalText;
                      confirmButton.disabled = false;
                    }
                  } catch (e) {
                    console.error("Error parsing response:", e);
                    // Show error toast
                    Toast.fire({
                      icon: "error",
                      title: "An unexpected error occurred",
                    });
                    // Restore button state
                    confirmButton.innerHTML = originalText;
                    confirmButton.disabled = false;
                  }
                },
                error: function (xhr, status, error) {
                  console.error("AJAX Error:", { xhr, status, error });
                  // Show error toast
                  Toast.fire({
                    icon: "error",
                    title: "Failed to connect to the server",
                  });
                  // Restore button state
                  confirmButton.innerHTML = originalText;
                  confirmButton.disabled = false;
                },
              });
            }, 3000); // Minimum 3 second loading state
          }
        });
      });

    // Email validation helper function
    function isValidEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    }

    // Display Settings Handler
    $("#displaySettingsBtn")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent event from bubbling up

        // Close the dropdown first
        $(".dropdown-menu").removeClass("show");

        // Show the modal
        Swal.fire({
          title: "Display Settings",
          html: `
                    <form id="displaySettingsForm" class="text-start">
                        <div class="mb-3">
                            <label class="form-label d-block">Theme</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="theme" id="lightTheme" checked>
                                <label class="btn btn-outline-primary" for="lightTheme">Light</label>
                                <input type="radio" class="btn-check" name="theme" id="darkTheme">
                                <label class="btn btn-outline-primary" for="darkTheme">Dark</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fontSize" class="form-label">Font Size</label>
                            <select class="form-select" id="fontSize">
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                    </form>
                `,
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-save me-1"></i>Save Changes',
          cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
          width: "500px",
          customClass: {
            container: "display-settings-modal",
            popup: "display-settings-modal",
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-secondary",
          },
          preConfirm: () => {
            const theme = $("#darkTheme").prop("checked") ? "dark" : "light";
            const fontSize = $("#fontSize").val();
            return { theme, fontSize };
          },
        }).then((result) => {
          if (result.isConfirmed) {
            // Show processing toast
            Toast.fire({
              icon: "success",
              title: "Display settings updated!",
            });
            // Handle the display settings changes
            // Add your logic to apply theme and font size
          }
        });
      });
  };

  // Main initialization
  const initialize = ($) => {
    try {
      // Only initialize once
      if (!window.adminInitialized) {
        initializeTables($);
        initializeEventHandlers($);
        window.adminInitialized = true;
      }
    } catch (error) {
      console.error("Error during initialization:", error);
    }
  };

  // Start initialization when dependencies are ready
  waitForDependencies(initialize);
})();
