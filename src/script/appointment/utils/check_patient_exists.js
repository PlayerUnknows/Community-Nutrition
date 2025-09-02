  
  // Function to check if patient exists in database
  function checkPatientExists(patientId, inputElement) {
    const validationMessage = document.getElementById("patient-validation-message");
    const guardianContainer = document.getElementById("guardian_container");
    
    // Show loading state
    validationMessage.innerHTML = '<div class="text-info"><i class="fas fa-spinner fa-spin"></i> Checking patient ID...</div>';
    validationMessage.style.display = "block";
    
    // Hide guardian container while checking
    if (guardianContainer) {
      guardianContainer.style.display = "none";
    }
    
    $.ajax({
      url: "/src/controllers/AppointmentController.php",
      type: "POST",
      data: {
        action: "checkPatientExists",
        user_id: patientId
      },
      success: function(response) {
        // Check if the response is successful and contains data
        if (!response.success || !response.data) {
          validationMessage.innerHTML = '<div class="text-warning"><i class="fas fa-exclamation-triangle"></i> Unable to verify patient ID</div>';
          if (guardianContainer) {
            guardianContainer.style.display = "none";
          }
          return;
        }
        
        const patientData = response.data;
        
        if (patientData.exists) {
          // Patient exists - show success message
          validationMessage.innerHTML = '<div class="text-success"><i class="fas fa-check-circle"></i> ' + patientData.message + '</div>';
          inputElement.classList.remove("is-invalid");
          inputElement.classList.add("is-valid");
          
          // Auto-fill patient name if available
          if (patientData.patient_name) {
            const fullNameField = document.getElementById("full_name");
            if (fullNameField && !fullNameField.value) {
              fullNameField.value = patientData.patient_name;
            }
          }
          
          // Show guardian dropdown for existing patient
          if (guardianContainer) {
            guardianContainer.style.display = "block";
            // Trigger guardian lookup using the utility function
            if (typeof loadGuardians === 'function') {
              loadGuardians(patientId);
            } else {
              console.warn('loadGuardians function not found, falling back to direct call');
              // Fallback: call the function directly if it's in the same scope
              if (typeof window.loadGuardians === 'function') {
                window.loadGuardians(patientId);
              }
            }
          }
        } else {
          // Patient doesn't exist - show error message
          validationMessage.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + patientData.message + '</div>';
          inputElement.classList.remove("is-valid");
          inputElement.classList.add("is-invalid");
          
          // Hide guardian container for non-existing patient
          if (guardianContainer) {
            guardianContainer.style.display = "none";
          }
        }
      },
      error: function() {
        validationMessage.innerHTML = '<div class="text-warning"><i class="fas fa-exclamation-triangle"></i> Unable to verify patient ID</div>';
        
        // Hide guardian container on error
        if (guardianContainer) {
          guardianContainer.style.display = "none";
        }
      }
    });
  }