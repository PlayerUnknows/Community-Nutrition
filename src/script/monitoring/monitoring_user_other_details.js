 
     // Make handleViewDetails globally accessible
  $("#monitoringTable").on("click", ".btn-view", function(event) { 
   var $button = $(this);
   currentPatientId = $button.data("checkup-id");

    $.ajax({
      url: "../controllers/MonitoringController.php?action=getMonitoringDetails",
      method: "GET",
      data: { 
        field: "checkup_unique_id",
        useLike: "false",
        value: currentPatientId 
      },
      success: function (response) {
        if (response.success && response.data && response.data.length > 0) {
          window.displayMonitoringDetails(response.data[0]); // Pass the first item from the array
          $("#monitoringDetailsModal").modal("show");
        } else {
          alert(
            response.message || "No monitoring details found"
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error fetching details:", error);
        alert("Failed to load patient details. Please try again.");
      },
    });
  })
 
 // Make displayMonitoringDetails globally accessible
  window.displayMonitoringDetails = function(details) {
 
    // Fix the height field mapping - it should go to growthStatus element
    $("#growthStatus").text(
      details.height ? parseFloat(details.height).toFixed(2) + " cm" : "N/A"
    );
    $("#weight").text(
      details.weight ? parseFloat(details.weight).toFixed(2) + " kg" : "N/A"
    );
    $("#bp").text(details.bp ? details.bp + " mmHg" : "N/A");
    $("#temperature").text(
      details.temperature
        ? parseFloat(details.temperature).toFixed(2) + " °C"
        : "N/A"
    );
    $("#findings").text(details.findings || "N/A");

    var appointmentDate = "N/A";
    if (details.date_of_appointment && details.date_of_appointment !== "0000-00-00") {
        var date = new Date(details.date_of_appointment);
        if (!isNaN(date.getTime())) {
          appointmentDate = date.toLocaleDateString();
        }
    }

    var appointmentTime = details.time_of_appointment || "N/A";
    var createdAt = "N/A";
    if (details.created_at) {

        var date = new Date(details.created_at);
        if (!isNaN(date.getTime())) {
          createdAt = date.toLocaleString();
        }
    
    }

    $("#appointmentDate").text(appointmentDate);
    $("#appointmentTime").text(appointmentTime);
    $("#place").text(details.place || "N/A");
    $("#createdAt").text(createdAt);
  }

