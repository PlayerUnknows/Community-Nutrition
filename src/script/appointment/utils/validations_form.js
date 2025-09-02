function validateForm() {
    const form = document.getElementById("appointmentForm");
    let isValid = true;

    if (form) {
      // Validate each required input
      form.querySelectorAll(".form-control[required]").forEach((input) => {
        if (!validateInput(input)) {
          isValid = false;
        }
      });
      
      // Additional check: ensure patient exists if user_id is valid
      const userIdInput = document.getElementById("user_id");
      if (userIdInput && userIdInput.value.trim()) {
        const patientIdPattern = /^PAT\d+$/;
        if (patientIdPattern.test(userIdInput.value.trim())) {
          // Check if the patient validation message shows an error
          const validationMessage = document.getElementById("patient-validation-message");
          if (validationMessage && validationMessage.innerHTML.includes("text-danger")) {
            isValid = false;
          }
        }
      }
    }

    return isValid;
  }