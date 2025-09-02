function loadGuardians(patientId) {
    const guardianContainer = document.getElementById("guardian_container");
    
    if (!guardianContainer) return;
    
    // First, get the guardian data
    $.ajax({
      url: "/src/controllers/AppointmentController.php",
      type: "POST",
      data: {
        action: "getGuardians",
        patient_id: patientId
      },
      success: function(response) {
        // Check if the response is successful and contains data
        if (!response.success || !response.data) {
          guardianContainer.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + (response.message || 'Failed to load guardian information') + '</div>';
          return;
        }
        
        const guardianData = response.data;
        
        if (guardianData.father || guardianData.mother) {
          // Create guardian dropdown with all options already loaded
          let guardianHtml = '<label class="form-label">Guardian</label>';
          guardianHtml += '<select class="form-control" id="guardian_select" name="guardian">';
        
          
          if (guardianData.father && guardianData.father.trim()) {
            guardianHtml += '<option value="' + guardianData.father + '">' + guardianData.father + ' (Father)</option>';
          }
          
          if (guardianData.mother && guardianData.mother.trim()) {
            guardianHtml += '<option value="' + guardianData.mother + '">' + guardianData.mother + ' (Mother)</option>';
          }
          
          guardianHtml += '</select>';
          guardianHtml += '<div class="form-text">Select the guardian who will accompany the patient</div>';
          
          guardianContainer.innerHTML = guardianHtml;
        } else {
          guardianContainer.innerHTML = '<div class="text-warning"><i class="fas fa-exclamation-triangle"></i> No guardian information available for this patient</div>';
        }
      },
      error: function() {
        guardianContainer.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle"></i> Failed to load guardian information</div>';
      }
    });
  }
