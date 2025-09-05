var appointmentManager = {
  init: function () {
    // Call fetchAppointmentManager to initialize the DataTable
    if (window.fetchAppointmentManager) {
      return window.fetchAppointmentManager.init();
      } else {
      console.error("fetchAppointmentManager not found!");
      return false;
    }
  },

  // Method to refresh the table
  refresh: function () {
    if (window.fetchAppointmentManager) {
      return window.fetchAppointmentManager.refresh();
    }
    return false;
  },
};
// end of var

// Function to validate edit input (global scope)
window.validateEditInput = function (input) {
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
        inputName.charAt(0).toUpperCase() + inputName.slice(1).replace("_", " ")
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

  // If all validations pass
  input.classList.add("is-valid");
  if (feedbackEl && feedbackEl.classList.contains("invalid-feedback")) {
    feedbackEl.style.display = "none";
  }
  return true;
};


// Wait for document ready and initialize
$(document).ready(function () {
  try {
    // Initialize fetchAppointmentManager instead
    if (window.fetchAppointmentManager) {
      window.fetchAppointmentManager.init();
    }

    $(document).off("click.bs.modal.data-api", '[data-bs-dismiss="modal"]');

  } catch (error) {
    console.error("Error in appointment initialization:", error);
  }
});

