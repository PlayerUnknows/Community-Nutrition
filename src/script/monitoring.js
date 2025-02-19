// console.log("Monitoring.js loaded");
$(document).ready(function () {
  var table = $("#monitoringTable").DataTable({
    processing: true,
    serverSide: false,
    pageLength: 5,
    lengthChange: true,
    lengthMenu: [
      [5, 10, 25, 50, -1],
      [5, 10, 25, 50, "All"],
    ],
    dom:
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    searching: true,
    responsive: true,
    autoWidth: false,
    scrollX: false,
    scrollY: "50vh",
    scrollCollapse: false,
    scroller: false,
    fixedHeader: false,
    columns: [
      {
        data: "patient_id",
        className: "dt-left",
      },
      {
        data: "patient_fam_id",
        className: "dt-left",
      },
      {
        data: "age",
        className: "dt-center",
      },
      {
        data: "sex",
        className: "dt-center",
      },
      {
        data: "weight",
        className: "dt-right",

        render: function (data) {
          return data ? parseFloat(data).toFixed(2) + " kg" : "";
        },
      },
      {
        data: "height",
        className: "dt-right",
        render: function (data) {
          return data ? parseFloat(data).toFixed(2) + " cm" : "";
        },
      },
      {
        data: "bp",
        className: "dt-center",
      },
      {
        data: "temperature",
        className: "dt-right",

        render: function (data) {
          return data ? parseFloat(data).toFixed(1) + " °C" : "";
        },
      },
      {
        data: null,
        className: "dt-center",

        render: function (data, type, row) {
          return (
            '<button class="btn btn-primary btn-sm btn-view" data-patient-id="' +
            row.patient_id +
            '"><i class="fas fa-eye"></i> View</button>'
          );
        },
      },
    ],
    ajax: {
      url: "../backend/fetch_monitoring.php",
      dataSrc: function (json) {
        if (json.status === "success") {
          return json.data;
        } else {
          console.error("Server error:", json.message);
          return [];
        }
      },
    },
    order: [[0, "desc"]],
    initComplete: function () {
      table.columns.adjust(); // Adjust the columns on load
    },
    
  });

   // Adjust columns on window resize
   $(window).on('resize', function() {
    table.columns.adjust().draw();
  });

  // Handle entries per page change
  $("#monitoringPerPage").on("change", function () {
    var val = parseInt($(this).val());
    table.page.len(val).draw();
  });

  // Handle search input
  var searchTimeout;
  $("#monitoringSearch").on("input", function () {
    clearTimeout(searchTimeout);
    var searchValue = this.value;
    searchTimeout = setTimeout(function () {
      table.search(searchValue).draw();
    }, 300);
  });

  // Store the current patient ID globally
  let currentPatientId = null;

  // Handle view details button click
  $("#monitoringTable").on("click", ".btn-view", function () {
    var row = $(this).closest("tr");
    currentPatientId = row.find("td:first").text(); // Get the patient ID from the first column
    console.log("Viewing details for patient:", currentPatientId);

    $.ajax({
      url: "../backend/get_monitoring_details.php",
      method: "GET",
      data: { id: currentPatientId },
      success: function (response) {
        if (response.status === "success" && response.data) {
          var details = response.data;

          $("#weightCategory").text(details.weight_category || "N/A");
          $("#bmiStatus").text(details.finding_bmi || "N/A");
          $("#growthStatus").text(details.finding_growth || "N/A");
          $("#armCircumference").text(details.arm_circumference || "N/A");
          $("#armStatus").text(details.arm_circumference_status || "N/A");
          $("#findings").text(details.findings || "N/A");

          var appointmentDate = details.date_of_appointment
            ? new Date(details.date_of_appointment).toLocaleDateString()
            : "N/A";
          var appointmentTime = details.time_of_appointment || "N/A";
          var createdAt = details.created_at
            ? new Date(details.created_at).toLocaleString()
            : "N/A";

          $("#appointmentDate").text(appointmentDate);
          $("#appointmentTime").text(appointmentTime);
          $("#place").text(details.place || "N/A");
          $("#createdAt").text(createdAt);

          $("#monitoringDetailsModal").modal("show");
        } else {
          alert(
            "Failed to load details: " + (response.message || "Unknown error")
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error loading details:", error);
        alert("Failed to load details. Please try again.");
      },
    });
  });

  // Handle export button click
  $("#exportMonitoringBtn").click(function () {
    window.location.href = "../backend/export_monitoring.php";
    // Add a small delay before reloading to allow the download to start
    // setTimeout(function () {
    //   window.location.reload();
    // }, 1000);
  });

  // Handle history button click
  $("#viewHistoryBtn").on("click", function () {
    // console.log("History button clicked");
    // console.log("Current patient ID:", currentPatientId);
    if (!currentPatientId) {
      console.error("No patient ID available");
      return;
    }

    // First hide the details modal
    $("#monitoringDetailsModal").modal("hide");

    $.ajax({
      url: "../backend/get_patient_checkups.php",
      method: "GET",
      data: { id: currentPatientId },
      success: function (response) {
        console.log("History response:", response);

        // Show the history modal
        $("#checkupHistoryModal").modal("show");

        if (response.status === "success") {
          if (response.data && response.data.length > 0) {
            // Show table, hide no history message
            $("#historyTableContainer").removeClass("d-none");
            $("#noHistoryMessage").addClass("d-none");
            populateHistoryTable(response.data);
          } else {
            // Hide table, show no history message
            $("#historyTableContainer").addClass("d-none");
            $("#noHistoryMessage").removeClass("d-none");
          }
        } else {
          // Handle error
          $("#historyTableContainer").addClass("d-none");
          $("#noHistoryMessage")
            .removeClass("d-none")
            .find("h5")
            .text("Error loading history");
        }
      },
      error: function (xhr, status, error) {
        console.error("Ajax error:", error);
        // Show error in modal
        $("#historyTableContainer").addClass("d-none");
        $("#noHistoryMessage")
          .removeClass("d-none")
          .find("h5")
          .text("Error loading history");
        $("#checkupHistoryModal").modal("show");
      },
    });
  });

  function populateHistoryTable(history) {
    console.log("Populating history table with:", history);
    const tbody = $("#historyTableBody");
    tbody.empty();

    history.forEach((record) => {
      const row = `
                <tr>
                    <td>${formatDate(record.date_of_appointment)}</td>
                    <td>${record.weight_category || "N/A"}</td>
                    <td>${record.finding_bmi || "N/A"}</td>
                    <td>${record.finding_growth || "N/A"}</td>
                    <td>${record.arm_circumference || "N/A"} (${
        record.arm_circumference_status || "N/A"
      })</td>
                    <td>${record.findings || "N/A"}</td>
                </tr>
            `;
      tbody.append(row);
    });
  }

  function formatDate(dateString) {
    if (!dateString) return "N/A";
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
  }

  // Handle file import
  $("#confirmImportBtn").click(function () {
    const fileInput = $("#importFile")[0];
    if (!fileInput.files.length) {
      alert("Please select a file to import");
      return;
    }

    const formData = new FormData();
    formData.append("importFile", fileInput.files[0]);

    $.ajax({
      url: "../backend/import_monitoring.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.status === "success") {
          alert(response.message);
          $("#importDataModal").modal("hide");
          // Reload the table data
          table.ajax.reload(null, false); // ✅ Refresh table without full reload
        } else {
          alert("Import failed: " + response.message);
        }
      },
      error: function () {
        alert("Import failed. Please try again.");
      },
    });
  });

  // Clear file input when modal is hidden
  $("#importDataModal").on("hidden.bs.modal", function () {
    $("#importFile").val("");
  });

  // Modal event handlers
  $("#checkupHistoryModal").on("show.bs.modal", function () {
    console.log("History modal is showing");
  });

  $("#checkupHistoryModal").on("hidden.bs.modal", function () {
    console.log("History modal is hidden, showing details modal");
    $("#monitoringDetailsModal").modal("show");
  });

  $("#monitoringDetailsModal").on("hidden.bs.modal", function () {
    console.log("Details modal is hidden, clearing patient ID");
    currentPatientId = null;
  });

  // Download Template button
  $("#downloadTemplateBtn").click(function () {
    window.location.href = "../backend/download_template.php";
    // Add a small delay before reloading to allow the download to start
    // setTimeout(function () {
    //   window.location.reload();
    // }, 1000);
  });

  // Handle window resize
  $(window).on("resize", function () {
    table.columns.adjust().draw();
  });
});
