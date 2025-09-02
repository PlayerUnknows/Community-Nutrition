$(document).ready(function() {
  const checkAndExtend = () => {
    if (window.appointmentManager) {
      // Extend the existing appointmentManager with cancel functionality
      window.appointmentManager.cancelAppointment = function (appointmentId) {
        console.log("Cancelling appointment:", appointmentId);
    
        $.ajax({
          url: "/src/controllers/AppointmentController.php",
          type: "POST",
          dataType: "json",
          data: {
            action: "cancel",
            appointment_id: appointmentId
          },
          success: function (response) {
            console.log("Cancel response:", response);
    
            if (response && response.success) {
              Swal.fire({
                icon: "success",
                title: "Success",
                text: response.message || "Appointment cancelled successfully"
              }).then(() => {
                // ✅ always refresh after cancel
                if (window.fetchAppointmentManager && window.fetchAppointmentManager.refresh) {
                  window.fetchAppointmentManager.refresh();
                }
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: response?.message || "Failed to cancel appointment"
              });
            }
          },
          error: function (xhr, status, error) {
            console.error("Error cancelling appointment:", error, xhr.responseText);
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Failed to cancel appointment. Please try again."
            });
          }
        });
      };
    } else {
      // If not available yet, wait a bit and try again
      setTimeout(checkAndExtend, 100);
    }
  };
  
  // Start checking for appointmentManager
  checkAndExtend();
});
  
  // ✅ event binding for cancel button
  $(document).ready(function () {
    $("#appointmentsTable").on("click", ".cancel-btn", function (e) {
      e.preventDefault();
      const appointmentId = $(this).data("id");
      console.log("Cancel button clicked for appointment:", appointmentId);
  
      Swal.fire({
        title: "Cancel Appointment",
        text: "Are you sure you want to cancel this appointment?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, cancel it!"
      }).then((result) => {
        if (result.isConfirmed) {
          if (typeof window.appointmentManager.cancelAppointment === "function") {
            window.appointmentManager.cancelAppointment(appointmentId);
          } else {
            console.error("appointmentManager.cancelAppointment not found!");
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Cancel functionality not available"
            });
          }
        }
      });
    });
  });
  