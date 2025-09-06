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
                url: "src/controllers/UserServices/update_profile.php",
                type: "POST",
                data: {
                  newEmail: result.value.newEmail,
                  currentPassword: result.value.currentPassword,
                  newPassword: result.value.newPassword,
                },
                success: function (response) {
                  try {
                    // Response is already a JavaScript object, no need to parse
                    const data = response;
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