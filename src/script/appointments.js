var appointmentManager = {
  init: function () {
    // First destroy any existing DataTable instance
    if ($.fn.DataTable.isDataTable("#appointmentsTable")) {
      $("#appointmentsTable").DataTable().destroy();
    }

    // Clear the table body
    $("#appointmentsTable tbody").empty();

    const table = $("#appointmentsTable").DataTable({
      processing: true,
      serverSide: false,
      responsive: true,
      ajax: {
        url: "/src/controllers/AppointmentController.php",
        type: "GET",
        data: {
          action: "getAll",
        },
        dataSrc: function (response) {
          if (!response || !response.data) {
            return [];
          }
          return response.data;
        },
        error: function (xhr, error, thrown) {
          return [];
        },
      },
      columns: [
        { data: "user_id", defaultContent: "" },
        { data: "patient_name", defaultContent: "" },
        { data: "date", defaultContent: "" },
        { data: "time", defaultContent: "" },
        { data: "description", defaultContent: "" },
        {
          data: null,
          defaultContent: "",
          render: function (data, type, row) {
            if (!row || !row.appointment_prikey) return "";
            const appointmentId = row.appointment_prikey;
            const isCancelled = row.status === "cancelled";
            return `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary edit-btn" data-id="${appointmentId}" ${
              isCancelled ? "disabled" : ""
            }>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm ${
                                  isCancelled ? "btn-secondary" : "btn-warning"
                                } cancel-btn" 
                                        data-id="${appointmentId}"
                                        ${isCancelled ? "disabled" : ""}>
                                    <i class="fas ${
                                      isCancelled ? "fa-ban" : "fa-times"
                                    }"></i> 
                                    ${isCancelled ? "Cancelled" : "Cancel"}
                                </button>
                            </div>
                        `;
          },
        },
        { data: "status", defaultContent: "" },
      ],
      order: [[1, "desc"]], // Order by date column descending
      pageLength: 5,
      lengthMenu: [
        [5, 10, 25, 50],
        [5, 10, 25, 50],
      ],
      language: {
        emptyTable: "No appointments found",
        zeroRecords: "No matching appointments found",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "Showing 0 to 0 of 0 entries",
        infoFiltered: "(filtered from _MAX_ total entries)",
      },
    });

    // Handle search input with sanitization
    $("#appointmentSearch").on("keyup", function (e) {
      e.preventDefault();
      const searchValue = $(this).val();
      // Use a timeout to prevent too many searches while typing
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        table.search(searchValue).draw();
      }, 300);
    });

    // Handle length change
    $("#appointmentsPerPage").on("change", function () {
      table.page.len($(this).val()).draw();
    });

    // Handle pagination clicks
    $("#appointmentsPagination").on("click", ".page-link", function (e) {
      e.preventDefault();
      const page = $(this).data("page");

      if (page === "prev") {
        table.page("previous").draw("page");
      } else if (page === "next") {
        table.page("next").draw("page");
      } else {
        table.page(parseInt(page)).draw("page");
      }
    });

    // Handle edit button clicks
    $("#appointmentsTable").on("click", ".edit-btn", function () {
      const appointmentId = $(this).data("id");
      // Get appointment details
      $.ajax({
        url: "/src/controllers/AppointmentController.php?action=getAppointment",
        type: "GET",
        data: { id: appointmentId },
        success: function (appointment) {
          // Create or get BS modal instance
          let bsEditModal = bootstrap.Modal.getInstance(
            document.getElementById("editAppointmentModal")
          );
          if (!bsEditModal) {
            bsEditModal = new bootstrap.Modal(
              document.getElementById("editAppointmentModal")
            );
          }

          // Populate form before showing
          $("#edit_appointment_id").val(appointment.appointment_prikey);
          $("#edit_user_id").val(appointment.user_id);
          $("#edit_patient_name").val(appointment.full_name);
          $("#edit_date").val(moment(appointment.date).format("YYYY-MM-DD"));
          $("#edit_time").val(appointment.time);
          $("#edit_description").val(appointment.description);

          // Reset form validation state - using the global function
          window.resetEditForm();

          // Show modal
          bsEditModal.show();

          // Remove any aria-hidden attribute to prevent accessibility warnings
          document
            .getElementById("editAppointmentModal")
            .removeAttribute("aria-hidden");

          // Apply validation to edit form inputs
          const editForm = document.getElementById("editAppointmentForm");
          if (editForm) {
            // Add validation on input change for each form control
            editForm.querySelectorAll(".form-control").forEach((input) => {
              // Skip readonly fields
              if (input.readOnly) return;

              // Remove existing event listeners
              input.removeEventListener(
                "blur",
                window.validateEditInputHandler
              );
              input.removeEventListener(
                "input",
                window.validateEditInputHandler
              );
              input.removeEventListener(
                "change",
                window.validateEditInputHandler
              );

              // Add event listeners
              input.addEventListener("blur", window.validateEditInputHandler);
              input.addEventListener("input", window.validateEditInputHandler);
              input.addEventListener("change", window.validateEditInputHandler);
            });
          }

          // Set focus on first editable field
          setTimeout(() => {
            const firstEditableInput = editForm.querySelector(
              "input:not([readonly])"
            );
            if (firstEditableInput) firstEditableInput.focus();
          }, 150);
        },
        error: function (xhr, status, error) {
          Swal.fire("Error!", "Failed to fetch appointment details.", "error");
        },
      });
    });

    // Handle delete button clicks
    $("#appointmentsTable").on("click", ".cancel-btn", function () {
      const appointmentId = $(this).data("id");
      Swal.fire({
        title: "Are you sure?",
        text: "This will cancel the appointment. You can't undo this action.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ffc107",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, cancel it!",
        cancelButtonText: "No, keep it",
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "/src/controllers/AppointmentController.php?action=cancel",
            type: "POST",
            data: { id: appointmentId },
            success: function (response) {
              Swal.fire(
                "Cancelled!",
                "The appointment has been cancelled.",
                "success"
              );
              table.ajax.reload();
            },
            error: function (xhr, status, error) {
              Swal.fire("Error!", "Failed to cancel appointment.", "error");
            },
          });
        }
      });
    });
  },
};

// Function to validate edit input (move to global scope)
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

// Helper function for validation events (move to global scope)
window.validateEditInputHandler = function () {
  window.validateEditInput(this);
};

// Function to reset edit form and button state (move to global scope)
window.resetEditForm = function () {
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

  const updateButton = document.getElementById("updateAppointment");
  if (updateButton) {
    updateButton.disabled = false;
    updateButton.className = "btn btn-primary";
    updateButton.innerHTML = "Update Appointment";
  }
};

// Wait for document ready and initialize
$(document).ready(function () {
  try {
    appointmentManager.init();

    // Override Bootstrap's data-bs-dismiss handler globally
    // This prevents the global Bootstrap event handlers from causing errors
    $(document).off("click.bs.modal.data-api", '[data-bs-dismiss="modal"]');

    // Get modal elements
    const addModalEl = document.getElementById("addAppointmentModal");
    const editModalEl = document.getElementById("editAppointmentModal");

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

    // Function to reset form and button state
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
      }

      const saveButton = document.getElementById("saveAppointment");
      if (saveButton) {
        saveButton.disabled = false;
        saveButton.className = "btn btn-primary";
        saveButton.innerHTML = "Save Appointment";
      }
    }

    // Function to validate a single input
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

      // If all validations pass
      input.classList.add("is-valid");
      if (feedbackEl && feedbackEl.classList.contains("invalid-feedback")) {
        feedbackEl.style.display = "none";
      }
      return true;
    }

    // Function to validate the entire form
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
      }

      return isValid;
    }

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

    // Function to show modal manually
    function showModal() {
      if (!addModalEl) return;

      // Show backdrop
      backdropEl.classList.add("show");

      // Show modal
      addModalEl.classList.add("show");
      addModalEl.style.display = "block";
      addModalEl.removeAttribute("aria-hidden");
      addModalEl.setAttribute("aria-modal", "true");
      addModalEl.setAttribute("role", "dialog");

      // Add body class
      document.body.classList.add("modal-open");
      document.body.style.overflow = "hidden";

      // Reset form
      resetForm();

      // Focus first input
      setTimeout(() => {
        const firstInput = addModalEl.querySelector("input, select, textarea");
        if (firstInput) firstInput.focus();
      }, 100);
    }

    // Function to hide modal manually
    function hideModal() {
      if (!addModalEl) return;

      // Hide backdrop
      backdropEl.classList.remove("show");

      // Hide modal
      addModalEl.classList.remove("show");
      addModalEl.style.display = "none";
      addModalEl.removeAttribute("aria-modal");
      addModalEl.removeAttribute("role");
      // DO NOT set aria-hidden true as it causes accessibility issues

      // Reset body
      document.body.classList.remove("modal-open");
      document.body.style.overflow = "";
      document.body.style.paddingRight = "";

      // Remove any existing bootstrap backdrops (cleanup)
      document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
    }

    // Handle edit modal close
    function hideEditModal() {
      if (!editModalEl) return;

      // Properly close modal with Bootstrap
      const bsModal = bootstrap.Modal.getInstance(editModalEl);
      if (bsModal) {
        bsModal.hide();
      }

      // Manual cleanup
      editModalEl.classList.remove("show");
      editModalEl.style.display = "none";
      editModalEl.removeAttribute("aria-modal");
      editModalEl.removeAttribute("role");
      // DO NOT set aria-hidden true as it causes accessibility issues

      // Clean up body
      document.body.classList.remove("modal-open");
      document.body.style.overflow = "";
      document.body.style.paddingRight = "";

      // Remove any existing bootstrap backdrops (cleanup)
      document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());

      // Reset form
      window.resetEditForm();
    }

    // Fix BS Modal accessibility by overriding its methods
    const originalModalHide = bootstrap.Modal.prototype.hide;
    bootstrap.Modal.prototype.hide = function () {
      // Remove aria-hidden to avoid accessibility warnings
      if (this._element) {
        this._element.removeAttribute("aria-hidden");
      }
      // Call the original method
      originalModalHide.call(this);
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

        // Show success state
        saveButton.innerHTML = '<i class="fas fa-check"></i> Saved!';
        saveButton.className = "btn btn-success";

        // Refresh table
        await appointmentManager.init();

        // Close modal after delay
        setTimeout(() => {
          hideModal();
        }, 1000);
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

    // Handle Update Appointment button click
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

        // Show success state
        updateButton.innerHTML = '<i class="fas fa-check"></i> Updated!';
        updateButton.className = "btn btn-success";

        // Refresh table
        await appointmentManager.init();

        // Close modal after delay
        setTimeout(() => {
          hideEditModal();
        }, 1000);
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

    // Handle close buttons - replace Bootstrap's handlers with our own
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

    // Handle close buttons for edit modal
    editModalEl
      .querySelectorAll('[data-bs-dismiss="modal"]')
      .forEach((button) => {
        button.addEventListener("click", function (e) {
          e.preventDefault();
          hideEditModal();
        });
      });

    // Handle ESC key
    document.addEventListener("keydown", function (e) {
      if (
        e.key === "Escape" &&
        addModalEl &&
        addModalEl.classList.contains("show")
      ) {
        hideModal();
      }
      if (
        e.key === "Escape" &&
        editModalEl &&
        editModalEl.classList.contains("show")
      ) {
        hideEditModal();
      }
    });

    // Handle backdrop click
    backdropEl.addEventListener("click", function (e) {
      if (e.target === backdropEl) {
        hideModal();
      }
    });

    // Initial cleanup
    hideModal();
  } catch (error) {
    console.error("Error in appointment initialization:", error);
  }
});
