function resetForm() {
    const form = document.getElementById("appointmentForm");
    if (form) {
      form.reset();
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
      
      // Reset patient validation message
      const validationMessage = document.getElementById("patient-validation-message");
      if (validationMessage) {
        validationMessage.style.display = "none";
        validationMessage.innerHTML = "";
      }
      
      // Hide guardian container
      const guardianContainer = document.getElementById("guardian_container");
      if (guardianContainer) {
        guardianContainer.style.display = "none";
        guardianContainer.innerHTML = "";
      }
    }

    const saveButton = document.getElementById("saveAppointment");
    if (saveButton) {
      saveButton.disabled = false;
      saveButton.className = "btn btn-primary";
      saveButton.innerHTML = "Save Appointment";
    }
  }

// Make function globally accessible
window.resetForm = resetForm;