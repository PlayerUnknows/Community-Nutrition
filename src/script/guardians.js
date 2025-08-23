document.addEventListener("DOMContentLoaded", function () {
  // Only set up the handler if the element exists
  const userIdField = document.getElementById("user_id");
  if (userIdField) {
    userIdField.addEventListener("blur", function () {
      const userId = this.value.trim();

      if (userId !== "") {
        // Show loading indicator
        const guardianContainer = document.getElementById("guardian_container");
        if (guardianContainer) {
          guardianContainer.style.display = "block";
          guardianContainer.innerHTML =
            '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading guardians...';
        }

        // Make the request to fetch guardians
        $.ajax({
          url: "/src/controllers/AppointmentController.php",
          type: "POST",
          data: {
            action: "getGuardians",
            user_id: userId,
          },
          dataType: "json",
          success: function (data) {
            // Check if response has guardian data
            if (data.father || data.mother) {
              if (guardianContainer) {
                // Reset container content
                guardianContainer.innerHTML = "";

                // Create label
                const label = document.createElement("label");
                label.setAttribute("for", "guardian_select");
                label.className = "form-label";
                label.textContent = "Select Guardian:";
                guardianContainer.appendChild(label);

                // Create select element
                const select = document.createElement("select");
                select.id = "guardian_select";
                select.name = "guardian";
                select.className = "form-select mb-3";

                // Add an empty default option
                const defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.textContent = "-- Select Guardian --";
                select.appendChild(defaultOption);

                // Add father as option if exists
                if (data.father && data.father.trim() !== "") {
                  const fatherOption = document.createElement("option");
                  fatherOption.value = data.father;
                  fatherOption.textContent = data.father + " (Father)";
                  select.appendChild(fatherOption);
                }

                // Add mother as option if exists
                if (data.mother && data.mother.trim() !== "") {
                  const motherOption = document.createElement("option");
                  motherOption.value = data.mother;
                  motherOption.textContent = data.mother + " (Mother)";
                  select.appendChild(motherOption);
                }

                // Add change event to update the full_name field
                select.addEventListener("change", function () {
                  const fullNameField = document.getElementById("full_name");
                  if (fullNameField && this.value) {
                    fullNameField.value = this.value;
                  }
                });

                // Add select to container
                guardianContainer.appendChild(select);

                // Display the container
                guardianContainer.style.display = "block";
              }
            } else {
              if (guardianContainer) {
                guardianContainer.innerHTML =
                  '<div class="alert alert-warning">No guardians found for this patient ID.</div>';
              }
            }
          },
          error: function (xhr) {
            let errorMessage = "Failed to fetch guardians.";

            // Attempt to parse the response JSON
            try {
              if (xhr.responseText) {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                  errorMessage = response.error;
                }
              }
            } catch (e) {
              // Silently handle parsing errors
            }

            if (guardianContainer) {
              guardianContainer.innerHTML =
                '<div class="alert alert-danger">' + errorMessage + "</div>";
            }
          },
        });
      } else {
        // If no user ID entered, hide guardian dropdown
        const guardianContainer = document.getElementById("guardian_container");
        if (guardianContainer) {
          guardianContainer.style.display = "none";
        }
      }
    });
  }
});
