 // Add export handler
 $("#exportMonitoringBtn").on("click", function () {
    $.ajax({
      url: "../controllers/MonitoringController.php?action=exportData",
      method: "GET",
      xhrFields: {
        responseType: "blob",
      },
      beforeSend: function () {
   
      },
      success: function (response, status, xhr) {

        // Check if we received an error message
        if (
          xhr
            .getResponseHeader("Content-Type")
            .indexOf("application/json") !== -1
        ) {
          // Handle JSON error response
          const reader = new FileReader();
          reader.onload = function () {
            const errorResponse = JSON.parse(this.result);
            console.error("Export error:", errorResponse);
            alert(errorResponse.message || "Failed to export data");
          };
          reader.readAsText(response);
          return;
        }

        // Handle successful CSV response
        const blob = new Blob([response], { type: "text/csv" });
        const link = document.createElement("a");
        link.href = window.URL.createObjectURL(blob);
        link.download =
          "monitoring_data_" + new Date().toISOString().slice(0, 10) + ".csv";

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      },
      error: function (xhr, status, error) {
        console.error("Export failed - Status:", status);
        console.error("Error:", error);
        console.error("Response:", xhr.responseText);
        alert("Failed to export data. Please check the console for details.");
      },
    });
  });