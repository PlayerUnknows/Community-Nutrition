function validateInput(input) {
    const value = input.value.trim();
    const inputName = input.name;
    const feedbackEl = input.nextElementSibling;

    // Reset previous validation state
    input.classList.remove("is-invalid");
    input.classList.remove("is-valid");

    // Check if empty
    if (!value) {
      input.classList.add("is-invalid");
      if (feedbackEl && feedbackEl.classList.contains("invalid-feedback")) {
        feedbackEl.textContent = `${
          inputName.charAt(0).toUpperCase() +
          inputName.slice(1).replace("_", " ")
        } is required`;
        feedbackEl.style.display = "block";
      }
      return false;
    }

    // Special validation for date
    if (inputName === "date") {
      const selectedDate = new Date(value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (selectedDate < today) {
        input.classList.add("is-invalid");
        if (feedbackEl && feedbackEl.classList.contains("invalid-feedback")) {
          feedbackEl.textContent = "Please select today or a future date";
          feedbackEl.style.display = "block";
        }
        return false;
      }
    }

    // Special validation for time
    if (inputName === "time") {
      const timeValue = value;
      const timeHour = parseInt(timeValue.split(":")[0]);

      // Check if time is during business hours (8 AM to 5 PM)
      if (timeHour < 8 || timeHour >= 17) {
        input.classList.add("is-invalid");
        if (feedbackEl && feedbackEl.classList.contains("invalid-feedback")) {
          feedbackEl.textContent =
            "Please select a time between 8:00 AM and 5:00 PM";
          feedbackEl.style.display = "block";
        }
        return false;
      }
    }

    // Special validation for user_id (patient ID)
    if (inputName === "user_id") {
      const patientIdPattern = /^PAT\d+$/;
      if (!patientIdPattern.test(value)) {
        input.classList.add("is-invalid");
        if (feedbackEl && feedbackEl.classList.contains("invalid-feedback")) {
          feedbackEl.textContent = "Please enter a valid patient ID (PAT followed by numbers)";
          feedbackEl.style.display = "block";
        }
        
        // Hide guardian container for invalid format
        const guardianContainer = document.getElementById("guardian_container");
        if (guardianContainer) {
          guardianContainer.style.display = "none";
        }
        
        return false;
      }
      
      // Check if patient exists in database (only if format is valid)
      if (patientIdPattern.test(value)) {
        debouncedCheckPatient(value, input);
      }
    }

    // If all validations pass
    input.classList.add("is-valid");
    if (feedbackEl && feedbackEl.classList.contains("invalid-feedback")) {
      feedbackEl.style.display = "none";
    }
    return true;
  }


  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

 

$("#patient_id").on("blur", function(){
    const patientId = $(this).val();
    if(patientId){
        checkPatientExists(patientId, $(this));
    }
});

  // Debounced version of checkPatientExists
  const debouncedCheckPatient = debounce(checkPatientExists, 500);
