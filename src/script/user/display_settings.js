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