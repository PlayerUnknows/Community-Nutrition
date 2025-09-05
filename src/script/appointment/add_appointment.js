$(document).ready(function() {
  // Get references to modal elements
  const addModalEl = document.getElementById("addAppointmentModal");
  // Create a backdrop div if needed
  let backdropEl = document.querySelector(".modal-custom-backdrop");
  
  if (!backdropEl) {
    backdropEl = document.createElement("div");
    backdropEl.className = "modal-custom-backdrop";
    document.body.appendChild(backdropEl);

    // Add styles for custom backdrop - only for add appointment modal
    const style = document.createElement("style");
    style.textContent = `
      .modal-custom-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
      }
      .modal-custom-backdrop.show {
        display: block;
      }
      /* Ensure Bootstrap modals have higher z-index */
      .modal.fade {
        z-index: 1055 !important;
      }
      .modal-backdrop {
        z-index: 1054 !important;
      }
      /* Custom error styles */
      .custom-error {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
      }
      .custom-error.show {
        display: block;
      }
      .form-control.has-error {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
      }
      .form-control.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.3-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.375rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
      }
    `;
    document.head.appendChild(style);
  }

  // Add real-time validation to form inputs
  const setupFormValidation = () => {
    const form = document.getElementById("appointmentForm");
    if (form) {
      // Add validation on input change
      form.querySelectorAll(".form-control").forEach((input) => {
        input.addEventListener("blur", function () {
          validateInput(this);
        });

        input.addEventListener("input", function () {
          validateInput(this);
        });

        // For date/time fields
        input.addEventListener("change", function () {
          validateInput(this);
        });
      });
    }
  };

  // Validation function for individual inputs
  const validateInput = (input) => {
    const fieldName = input.name;
    const value = input.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Remove existing error styling
    input.classList.remove('has-error');
    hideError(input);

    // Validation rules
    switch (fieldName) {
      case 'user_id':
        if (!value) {
          isValid = false;
          errorMessage = 'Patient ID is required';
        } else if (!/^PAT\d+/.test(value)) {
          isValid = false;
          errorMessage = 'Patient ID must start with PAT followed by numbers';
        } else if (value.length < 15) {
          isValid = false;
          errorMessage = 'Patient ID must be at least 15 characters long';
        }
        break;
      
      case 'full_name':
        if (!value) {
          isValid = false;
          errorMessage = 'Full name is required';
        } else if (value.length < 2) {
          isValid = false;
          errorMessage = 'Full name must be at least 2 characters long';
        }
        break;
      
      case 'date':
        if (!value) {
          isValid = false;
          errorMessage = 'Date is required';
        } else {
          const selectedDate = new Date(value);
          const today = new Date();
          today.setHours(0, 0, 0, 0);
          
          if (selectedDate < today) {
            isValid = false;
            errorMessage = 'Please select today or a future date';
          }
        }
        break;
      
      case 'time':
        if (!value) {
          isValid = false;
          errorMessage = 'Time is required';
        }
        break;
      
      case 'description':
        if (!value) {
          isValid = false;
          errorMessage = 'Description is required';
        } else if (value.length < 10) {
          isValid = false;
          errorMessage = 'Description must be at least 10 characters long';
        }
        break;
    }

    // Apply validation result
    if (!isValid) {
      input.classList.add('has-error');
      showError(input, errorMessage);
    } else {
      input.classList.remove('has-error');
      hideError(input);
    }

    return isValid;
  };

  // Show error message under input
  const showError = (input, message) => {
    // Remove existing error message
    hideError(input);
    
    // Create error element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'custom-error show';
    errorDiv.textContent = message;
    
    // Insert after the input
    input.parentNode.appendChild(errorDiv);
  };

  // Hide error message
  const hideError = (input) => {
    const existingError = input.parentNode.querySelector('.custom-error');
    if (existingError) {
      existingError.remove();
    }
  };

  // Validate entire form
  const validateForm = () => {
    const form = document.getElementById("appointmentForm");
    if (!form) return false;

    let isValid = true;
    const inputs = form.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
      if (!validateInput(input)) {
        isValid = false;
      }
    });

    return isValid;
  };

  // Initial setup of form validation
  setupFormValidation();

  // Hide custom backdrop when Bootstrap modals are shown to prevent conflicts
  $(document).on('show.bs.modal', function(e) {
    // Don't hide backdrop for our own add appointment modal
    if (e.target.id === 'addAppointmentModal') {
      return;
    }
    
    if (backdropEl && backdropEl.classList.contains('show')) {
      console.log('Hiding custom backdrop for Bootstrap modal:', e.target.id);
      backdropEl.classList.remove('show');
    }
  });



  // Handle Patient ID input to check patient existence and load guardians
  $("#user_id").on("input", function() {
    const patientId = $(this).val().trim();
    
    console.log('Patient ID input detected:', patientId);
    
    // Clear previous validation state
    $(this).removeClass("is-valid is-invalid");
    
    // Only check if patient ID is long enough
    if (patientId.length >= 15) {

      // Check if patient exists and load guardians
      if (typeof checkPatientExists === 'function') {
        checkPatientExists(patientId, this);
      } else {
        console.error('checkPatientExists function not found - check if check_patient_exists.js is loaded');
      }
    } else {
      console.log('Patient ID too short, hiding guardian container');
      // Hide guardian container and validation message for short IDs
      $("#guardian_container").hide();
      $("#patient-validation-message").hide();
    }
  });

  // Also handle blur event for when user finishes typing
  $("#user_id").on("blur", function() {
    const patientId = $(this).val().trim();
    
    console.log('Patient ID field lost focus, value:', patientId);
    
    if (patientId.length >= 15) {
      console.log('Patient ID length sufficient on blur, checking patient existence...');
      // Check if patient exists and load guardians
      if (typeof checkPatientExists === 'function') {
        checkPatientExists(patientId, this);
      } else {
        console.error('checkPatientExists function not found on blur');
      }
    }
  });

  // Handle new appointment button
  const newAppointmentBtn = document.querySelector(
    '[data-bs-target="#addAppointmentModal"]'
  );
  if (newAppointmentBtn) {
    // Remove bootstrap's default handlers
    newAppointmentBtn.removeAttribute("data-bs-toggle");

    newAppointmentBtn.addEventListener("click", function (e) {
      e.preventDefault();
      
      // Ensure any existing Bootstrap modals are properly closed
      $('.modal').modal('hide');
      
      // Wait a bit for Bootstrap modals to close, then show our modal
      setTimeout(() => {
        showAppointmentModal();
        // Setup validation again in case DOM has changed
        setupFormValidation();
      }, 150);
    });
  }

  // Handle save button
  $("#saveAppointment").on("click", async function (e) {
    e.preventDefault();
    const saveButton = this;

    // First validate the entire form
    if (!validateForm()) {
      // Form is invalid - scroll to first error
      const firstError = document.querySelector('.form-control.has-error');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
      }
      return;
    }

    // Get form data
    const formData = {
      user_id: $("#user_id").val(),
      full_name: $("#full_name").val(),
      date: $("#date").val(),
      time: $("#time").val(),
      description: $("#description").val(),
      guardian: $("#guardian_select").val() || null,
    };

    // Show loading state
    saveButton.disabled = true;
    saveButton.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

    try {
      const response = await $.ajax({
        url: "/src/controllers/AppointmentController.php",
        type: "POST",
        data: {
          action: "add",
          ...formData,
        },
      });

      // Show success state with appointment details
      saveButton.innerHTML = '<i class="fas fa-check"></i> Saved!';
      saveButton.className = "btn btn-success";

      // Show detailed success message with appointment details
      if (response.appointment_details) {
        const details = response.appointment_details;
        Swal.fire({
          icon: "success",
          title: "Appointment Created Successfully!",
          html: `
            <div class="text-start">
              <p><strong>Appointment Details:</strong></p>
              <ul class="text-start">
                <li><strong>Patient:</strong> ${details.patient_name}</li>
                <li><strong>Patient ID:</strong> ${details.patient_id}</li>
                <li><strong>Date:</strong> ${details.appointment_date}</li>
                <li><strong>Time:</strong> ${details.appointment_time}</li>
                <li><strong>Description:</strong> ${details.description}</li>
                ${details.guardian && details.guardian !== 'Not specified' ? `<li><strong>Guardian:</strong> ${details.guardian}</li>` : ''}
              </ul>
            </div>
          `,
          confirmButtonText: "OK"
        });
      } else {
        // Show simple success message
        Swal.fire({
          icon: "success",
          title: "Appointment Created!",
          text: "Appointment created successfully!",
          confirmButtonText: "OK"
        });
      }

      // Refresh table
      if (window.fetchAppointmentManager) {
        await window.fetchAppointmentManager.refresh();
      }
      
      // Close modal after delay
      setTimeout(() => {
        hideAppointmentModal();
      }, 2000);
    } catch (error) {
      console.log("Appointment creation failed:", error);

      // Extract error message
      let errorMessage = "Failed to create appointment. Please try again.";

      if (error.responseJSON && error.responseJSON.error) {
        errorMessage = error.responseJSON.error;
      } else if (error.statusText) {
        errorMessage = `Server error: ${error.statusText}`;
      }

      // Show user-friendly error
      Swal.fire({
        icon: "error",
        title: "Cannot Create Appointment",
        text: errorMessage,
      });

      // Update button state
      saveButton.innerHTML = '<i class="fas fa-times"></i> Failed';
      saveButton.className = "btn btn-danger";

      setTimeout(() => {
        saveButton.disabled = false;
        saveButton.className = "btn btn-primary";
        saveButton.innerHTML = "Save Appointment";
      }, 2000);
    }
  });

  // Handle close buttons - replace Bootstrap's handlers with our own
  if (addModalEl) {
    addModalEl
      .querySelectorAll('[data-bs-dismiss="modal"]')
      .forEach((button) => {
        // Remove bootstrap's default handler
        button.removeAttribute("data-bs-dismiss");

        button.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation(); // Stop event propagation
          hideAppointmentModal();
        });
      });
  }

  // Also handle when the add appointment modal is hidden to ensure backdrop is cleaned up
  $('#addAppointmentModal').on('hidden.bs.modal', function() {
    if (backdropEl && backdropEl.classList.contains('show')) {
      backdropEl.classList.remove('show');
    }
  });

  // Clean up custom backdrop when any modal is hidden
  $(document).on('hidden.bs.modal', function(e) {
    // Don't interfere with our own add appointment modal
    if (e.target.id === 'addAppointmentModal') {
      return;
    }
    
    // Ensure our custom backdrop is hidden
    if (backdropEl && backdropEl.classList.contains('show')) {
      console.log('Cleaning up custom backdrop after Bootstrap modal hidden:', e.target.id);
      backdropEl.classList.remove('show');
    }
  });

  // Handle ESC key
  document.addEventListener("keydown", function (e) {
    if (
      e.key === "Escape" &&
      addModalEl &&
      addModalEl.classList.contains("show")
    ) {
      hideAppointmentModal();
    }
  });

  // Handle backdrop click
  if (backdropEl) {
    backdropEl.addEventListener("click", function (e) {
      if (e.target === backdropEl) {
        hideAppointmentModal();
      }
    });
  }

  // Initial cleanup
  hideAppointmentModal();
}); // Close document ready function