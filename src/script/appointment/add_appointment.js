$(document).ready(function() {
  // Get references to modal elements
  const addModalEl = document.getElementById("addAppointmentModal");
  
  // Create a backdrop div if needed
  let backdropEl = document.querySelector(".modal-custom-backdrop");
  if (!backdropEl) {
    backdropEl = document.createElement("div");
    backdropEl.className = "modal-custom-backdrop";
    document.body.appendChild(backdropEl);

    // Add styles for custom backdrop
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
      .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
      }
      .was-validated .form-control:invalid,
      .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
      }
      .was-validated .form-control:valid,
      .form-control.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
      }
      .is-invalid ~ .invalid-feedback {
        display: block;
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

  // Initial setup of form validation
  setupFormValidation();

  // Handle new appointment button
  const newAppointmentBtn = document.querySelector(
    '[data-bs-target="#addAppointmentModal"]'
  );
  if (newAppointmentBtn) {
    // Remove bootstrap's default handlers
    newAppointmentBtn.removeAttribute("data-bs-toggle");

    newAppointmentBtn.addEventListener("click", function (e) {
      e.preventDefault();
      showModal();
      // Setup validation again in case DOM has changed
      setupFormValidation();
    });
  }

  // Handle save button
  $("#saveAppointment").on("click", async function (e) {
    e.preventDefault();
    const saveButton = this;

    // First validate the entire form
    if (!validateForm()) {
      // Form is invalid - show all errors
      const form = document.getElementById("appointmentForm");
      if (form) {
        form.classList.add("was-validated");
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
        hideModal();
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
          hideModal();
        });
      });
  }

  // Handle ESC key
  document.addEventListener("keydown", function (e) {
    if (
      e.key === "Escape" &&
      addModalEl &&
      addModalEl.classList.contains("show")
    ) {
      hideModal();
    }
  });

  // Handle backdrop click
  if (backdropEl) {
    backdropEl.addEventListener("click", function (e) {
      if (e.target === backdropEl) {
        hideModal();
      }
    });
  }

  // Initial cleanup
  hideModal();
}); // Close document ready function