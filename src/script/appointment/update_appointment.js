
 // Get reference to the edit modal element
const editModalEl = document.getElementById("editAppointmentModal");

// Function to validate the edit form
function validateEditForm() {
    const form = document.getElementById("editAppointmentForm");
    let isValid = true;

    if (form) {
      // Validate each required input that's not readonly
      form
        .querySelectorAll(".form-control[required]:not([readonly])")
        .forEach((input) => {
          if (!window.validateEditInput(input)) {
            isValid = false;
          }
        });
    }

    return isValid;
  }




$("#updateAppointment").on("click", async function (e) {
    e.preventDefault();
    const updateButton = this;

    // First validate the entire form
    if (!validateEditForm()) {
      // Form is invalid - show all errors
      const form = document.getElementById("editAppointmentForm");
      if (form) {
        form.classList.add("was-validated");
      }
      return;
    }

    // Get form data
    const formData = {
      id: $("#edit_appointment_id").val(),
      user_id: $("#edit_user_id").val(),
      date: $("#edit_date").val(),
      time: $("#edit_time").val(),
      guardian: $("#edit_guardian").val(),
      description: $("#edit_description").val(),
    };

    // Show loading state
    updateButton.disabled = true;
    updateButton.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

    try {
      const response = await $.ajax({
        url: "/src/controllers/AppointmentController.php?action=update",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(formData),
      });

      // Show success state with change details
      let successMessage = "Appointment updated successfully!";
      
      // Get current guardian selection for display
      const currentGuardian = $("#edit_guardian").val() || "Not specified";
      
      // If there are change details, show them
      if (response.changes && response.changes.length > 0) {
        const changeDetails = response.changes.map(change => 
          `${change.field}: '${change.old_value}' → '${change.new_value}'`
        ).join('\n');
        
        successMessage = `Appointment updated successfully!\n\nChanges made:\n${changeDetails}`;
        
        // Show detailed success message
        Swal.fire({
          icon: "success",
          title: "Appointment Updated!",
          text: `Updated: ${response.change_summary}`,
          html: `
            <div class="text-start">
              <p><strong>Changes made:</strong></p>
              <ul class="text-start">
                ${response.changes.map(change => 
                  `<li><strong>${change.field}:</strong> '${change.old_value}' → '${change.new_value}'</li>`
                ).join('')}
              </ul>
              <p class="mt-3"><strong>Current Guardian:</strong> ${currentGuardian}</p>
            </div>
          `,
          confirmButtonText: "OK"
        });
      } else {
        // Show simple success message with guardian info
        Swal.fire({
          icon: "success",
          title: "Appointment Updated!",
          text: "Appointment updated successfully!",
          html: `
            <div class="text-start">
              <p><strong>Current Guardian:</strong> ${currentGuardian}</p>
            </div>
          `,
          confirmButtonText: "OK"
        });
      }
      
      updateButton.innerHTML = '<i class="fas fa-check"></i> Updated!';
      updateButton.className = "btn btn-success";

      // Refresh table
      if (window.fetchAppointmentManager) {
        await window.fetchAppointmentManager.refresh();
      }

      // Close modal after delay
      setTimeout(() => {
        hideEditModal();
      }, 2000);
    } catch (error) {
      console.log("Appointment update failed:", error);

      // Extract error message
      let errorMessage = "Failed to update appointment. Please try again.";

      if (error.responseJSON && error.responseJSON.error) {
        errorMessage = error.responseJSON.error;
      } else if (error.statusText) {
        errorMessage = `Server error: ${error.statusText}`;
      }

      // Show user-friendly error
      Swal.fire({
        icon: "error",
        title: "Cannot Update Appointment",
        text: errorMessage,
      });

      // Reset button state
      updateButton.innerHTML = '<i class="fas fa-times"></i> Failed';
      updateButton.className = "btn btn-danger";

      setTimeout(() => {
        updateButton.disabled = false;
        updateButton.className = "btn btn-primary";
        updateButton.innerHTML = "Update Appointment";
      }, 2000);
    }
  });


     // Function to reset edit form and button state
     function resetEditForm() {
       const form = document.getElementById("editAppointmentForm");
       if (form) {
         form.classList.remove("was-validated");

         // Reset all validation states
         form.querySelectorAll(".form-control").forEach((input) => {
           input.classList.remove("is-invalid");
           input.classList.remove("is-valid");
         });

         // Reset all feedback messages
         form.querySelectorAll(".invalid-feedback").forEach((feedback) => {
           feedback.style.display = "none";
         });
       }
     }

           // Handle edit modal close
      function hideEditModal() {
         if (!editModalEl) return;
   
         // Use Bootstrap's built-in modal closing to avoid accessibility issues
         const bsModal = bootstrap.Modal.getInstance(editModalEl);
         if (bsModal) {
           // Let Bootstrap handle the closing properly
           bsModal.hide();
           
           // Wait for Bootstrap to finish closing, then reset form
           setTimeout(() => {
             resetEditForm();
           }, 150);
         } else {
           // Fallback if Bootstrap modal instance not found
           editModalEl.style.display = "none";
           document.body.classList.remove("modal-open");
           
           // Reset form
           resetEditForm();
         }
       }

       // Set up edit modal event handlers
       $(document).ready(function() {
         // Handle close buttons for edit modal
         if (editModalEl) {
           editModalEl
             .querySelectorAll('[data-bs-dismiss="modal"]')
             .forEach((button) => {
               button.addEventListener("click", function (e) {
                 e.preventDefault();
                 hideEditModal();
               });
             });
         }

         // Handle ESC key for edit modal
         document.addEventListener("keydown", function (e) {
           if (
             e.key === "Escape" &&
             editModalEl &&
             editModalEl.classList.contains("show")
           ) {
             hideEditModal();
           }
         });
       });



