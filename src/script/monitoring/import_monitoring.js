   // Make setButtonLoading globally accessible
  window.setButtonLoading = function(button, isLoading) {
    if (isLoading) {
      button.disabled = true;
      button.innerHTML = `
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          Importing...
        `;
    } else {
      button.disabled = false;
      button.innerHTML = "Import";
    }
  }




$("#confirmImportBtn").on("click", function(event) {
    const fileInput = $("#importFile")[0];
    if (!fileInput.files.length) {
      window.showToast("Please select a file to import", "error");
      return;
    }

    const importButton = document.getElementById("confirmImportBtn");
            window.setButtonLoading(importButton, true);

    const formData = new FormData();
    formData.append("importFile", fileInput.files[0]);
    $.ajax({
      url: "../controllers/MonitoringController.php?action=importData",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        // Simulate minimum 3 second loading
        setTimeout(() => {
          if (response.success === true) {
            // Show success message
            window.showToast(response.message, "success");
            
            // Reset the file input
            $("#importFile").val("");
            
            // Refresh the table immediately
            if (window.monitoringTable) {
              window.monitoringTable.ajax.reload();
            }
            
            // Let user close modal manually - don't force close it
          } else {
            window.showToast(response.message || "Import failed", "error");
          }
          window.setButtonLoading(importButton, false);
        }, 3000);
      },
      error: function (xhr, status, error) {
        setTimeout(() => {
          let errorMessage = "Import failed. Please try again.";
          
          // Try to get the actual error message from the response
          if (xhr.responseText) {
            try {
              const response = JSON.parse(xhr.responseText);
              if (response.message) {
                errorMessage = response.message;
              }
            } catch (e) {
              // If response is not JSON, use the raw response text
              errorMessage = xhr.responseText.substring(0, 200) + "...";
            }
          }
          
          window.showToast(errorMessage, "error");
          window.setButtonLoading(importButton, false);
        }, 3000);
      },
    });
  })