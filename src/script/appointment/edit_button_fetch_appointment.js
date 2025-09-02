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
  $("#appointmentsTable").on("click", ".edit-btn", function (e) {
    e.preventDefault();
    const appointmentId = $(this).data("id");

    
    // Fetch appointment data for editing
    $.ajax({
      url: "/src/controllers/AppointmentController.php",
      type: "POST",
      data: {
        action: "getAppointmentToEdit",
        appointment_id: appointmentId
      },
      success: function(response) {
        
        if (response.success && response.data) {
          const appointment = response.data;
          
          // Populate the edit form
          $("#edit_appointment_id").val(appointment.appointment_prikey);
          $("#edit_user_id").val(appointment.user_id);
          $("#edit_patient_name").val(appointment.patient_name);
          $("#edit_date").val(appointment.date);
          $("#edit_time").val(appointment.time);
          $("#edit_description").val(appointment.description);
          $("#edit_guardian").val(appointment.guardian || "");
          
                       // Load guardians for the patient
           loadGuardiansForEdit(appointment.user_id, appointment.guardian);
          
          // Show the edit modal
          $("#editAppointmentModal").modal("show");
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.message || "Failed to load appointment data for editing"
          });
        }
      },
      error: function(xhr, status, error) {
        console.error("Error fetching edit data:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Failed to load appointment data for editing"
        });
      }
    });
  });



  // Function to load guardians for edit form (global scope)
function loadGuardiansForEdit(patientId, currentGuardian = "") {
    const editGuardianSelect = document.getElementById("edit_guardian");
    if (!editGuardianSelect) {
      console.error("edit_guardian element not found!");
      return;
    }
  
    $.ajax({
      url: "/src/controllers/AppointmentController.php",
      type: "POST",
      data: {
        action: "getGuardians",
        patient_id: patientId
      },
      success: function(response) {
        
        if (response.success && response.data) {
          const guardianData = response.data;
          
          if (guardianData.father || guardianData.mother) {
            if (guardianData.father && guardianData.father.trim()) {
              const fatherOption = document.createElement('option');
              fatherOption.value = guardianData.father;
              fatherOption.textContent = guardianData.father + ' (Father)';
              editGuardianSelect.appendChild(fatherOption);
            }
            
            if (guardianData.mother && guardianData.mother.trim()) {
              const motherOption = document.createElement('option');
              motherOption.value = guardianData.mother;
              motherOption.textContent = guardianData.mother + ' (Mother)';
              editGuardianSelect.appendChild(motherOption);
            }
            
            // After adding all options, set the current guardian value
            if (currentGuardian && currentGuardian.trim()) {
              editGuardianSelect.value = currentGuardian;
            }
          } else {
            // Add a message option if no guardians available
            const noGuardianOption = document.createElement('option');
            noGuardianOption.value = "";
            noGuardianOption.textContent = "No guardian information available";
            noGuardianOption.disabled = true;
            editGuardianSelect.appendChild(noGuardianOption);
          }
        } else {
          // Add a message option if response is not successful
          const noGuardianOption = document.createElement('option');
          noGuardianOption.value = "";
          noGuardianOption.textContent = response.message || "No guardian information available";
          noGuardianOption.disabled = true;
          editGuardianSelect.appendChild(noGuardianOption);
        }
      },
      error: function(xhr, status, error) {
        console.error("Error loading guardians:", {xhr, status, error});
        // Add error option
        const errorOption = document.createElement('option');
        errorOption.value = "";
        errorOption.textContent = "Failed to load guardian information";
        errorOption.disabled = true;
        editGuardianSelect.appendChild(errorOption);
      }
    });
  }