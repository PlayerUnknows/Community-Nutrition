// Create a global namespace for monitoring
window.MonitoringModule = (function () {
  let monitoringTable = null;
  let currentPatientId = null;
  let isInitialized = false;

  function initializeToastStyles() {
    // Check if styles are already added
    if (!document.getElementById("monitoring-toast-styles")) {
      const style = document.createElement("style");
      style.id = "monitoring-toast-styles";
      style.textContent = `
          .toast-container {
            z-index: 1060;
          }
          .toast {
            opacity: 1 !important;
            margin-bottom: 1rem;
          }
          .toast.bg-success {
            background-color: #28a745 !important;
          }
          .toast.bg-danger {
            background-color: #dc3545 !important;
          }
        `;
      document.head.appendChild(style);
    }
  }

  function displayMonitoringDetails(details) {
    // Add detailed console logging
    console.log("Full details object:", details);
    console.log("Height value:", details.height);

    $("#weightCategory").text(details.weight_category || "N/A");
    $("#bmiStatus").text(details.finding_bmi || "N/A");
    $("#growthStatus").text(
      details ? parseFloat(details.height).toFixed(2) + " cm" : "N/A"
    );
    $("#weight").text(
      details.weight ? parseFloat(details.weight).toFixed(2) + " kg" : "N/A"
    );
    $("#armStatus").text(details.arm_circumference_status || "N/A");
    $("#temperature").text(
      details.temperature
        ? parseFloat(details.temperature).toFixed(2) + " °C"
        : "N/A"
    );
    $("#bp").text(details.bp ? details.bp + " mmHg" : "N/A");
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
  }

  function handleViewDetails($button) {
    currentPatientId = $button.data("patient-id");

    $.ajax({
      url: "../backend/get_monitoring_details.php",
      method: "GET",
      data: { id: currentPatientId },
      success: function (response) {
        if (response.status === "success" && response.data) {
          displayMonitoringDetails(response.data);
          $("#monitoringDetailsModal").modal("show");
        } else {
          alert(
            "Failed to load details: " + (response.message || "Unknown error")
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error fetching details:", error);
        alert("Failed to load patient details. Please try again.");
      },
    });
  }

  function cleanupModalBackdrop() {
    // Only remove backdrop if it exists
    const backdrop = document.querySelector(".modal-backdrop");
    if (backdrop) {
      backdrop.remove();
    }

    // Only reset body styles if modal-open class exists
    if (document.body.classList.contains("modal-open")) {
      document.body.classList.remove("modal-open");
      document.body.style.removeProperty("overflow");
      document.body.style.removeProperty("padding-right");
    }
  }

  function showToast(message, type = "success") {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector(".toast-container");
    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className =
        "toast-container position-fixed bottom-0 end-0 p-3";
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = "toast-" + Date.now();
    const toast = document.createElement("div");
    toast.className = `toast align-items-center border-0 ${
      type === "success" ? "bg-success" : "bg-danger"
    } text-white`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");
    toast.setAttribute("id", toastId);

    // Create toast content
    toast.innerHTML = `
          <div class="d-flex">
              <div class="toast-body">
                  ${message}
              </div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
      `;

    // Add toast to container
    toastContainer.appendChild(toast);

    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, {
      animation: true,
      autohide: true,
      delay: 3000,
    });
    bsToast.show();

    // Remove toast after it's hidden
    toast.addEventListener("hidden.bs.toast", function () {
      toast.remove();
    });
  }

  function setButtonLoading(button, isLoading) {
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

  function handleFileImport() {
    const fileInput = $("#importFile")[0];
    if (!fileInput.files.length) {
      showToast("Please select a file to import", "error");
      return;
    }

    const importButton = document.getElementById("confirmImportBtn");
    setButtonLoading(importButton, true);

    const formData = new FormData();
    formData.append("importFile", fileInput.files[0]);

    $.ajax({
      url: "../backend/import_monitoring.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        // Simulate minimum 3 second loading
        setTimeout(() => {
          if (response.status === "success") {
            const modalElement = document.getElementById("importDataModal");
            if (modalElement) {
              const modal = bootstrap.Modal.getInstance(modalElement);
              if (modal) {
                // Reset the file input
                $("#importFile").val("");

                // Hide modal
                modal.hide();

                // Show success message after modal is hidden
                $(modalElement).one("hidden.bs.modal", function () {
                  showToast(response.message, "success");
                  // Remove backdrop and cleanup
                  $(".modal-backdrop").remove();
                  document.body.classList.remove("modal-open");
                  document.body.style.removeProperty("overflow");
                  document.body.style.removeProperty("padding-right");
                });
              }
            }
            if (monitoringTable) {
              monitoringTable.ajax.reload();
            }
          } else {
            showToast(response.message || "Import failed", "error");
          }
          setButtonLoading(importButton, false);
        }, 3000);
      },
      error: function () {
        setTimeout(() => {
          showToast("Import failed. Please try again.", "error");
          setButtonLoading(importButton, false);
        }, 3000);
      },
    });
  }

  function initializeTable() {
    try {
      // Always destroy existing instance first
      if (monitoringTable) {
        monitoringTable.destroy();
        monitoringTable = null;
        $("#monitoringTable tbody").empty();
      }

      // Create new instance
      monitoringTable = $("#monitoringTable").DataTable({
        retrieve: true,
        processing: true,
        serverSide: false,
        pageLength: 5,
        lengthChange: false, // Disable built-in length change
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
        scrollX: true,
        scrollY: "50vh",
        scrollCollapse: true,
        scroller: false,
        fixedHeader: true,
        columns: [
          {
            data: "patient_id",
            className: "dt-left",
            width: "150px",
          },
          {
            data: "patient_name",
            className: "dt-left",
            width: "150px",
          },
          {
            data: "patient_fam_id",
            className: "dt-left",
            width: "150px",
          },
          {
            data: "age",
            className: "dt-center",
            width: "50px",
          },
          {
            data: "sex",
            className: "dt-center",
            width: "80px",
          },
          {
            data: "finding_bmi",
            className: "dt-center",
            width: "150px",
          },
          {
            data: "finding_growth",
            className: "dt-center",
            width: "150px",
          },
          {
            data: "arm_circumference_status",
            className: "dt-center",
            width: "150px",
          },
          {
            data: null,
            className: "dt-center",
            width: "100px",
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
            return json.status === "success" ? json.data : [];
          },
        },
        order: [[0, "desc"]],
        initComplete: function () {
          setupEventHandlers();
          setupModalHandlers();
          this.api().columns.adjust();

          // Setup custom search
          $("#monitoringSearch").on("keyup", function () {
            monitoringTable.search(this.value).draw();
          });

          // Setup custom length menu
          $("#monitoringPerPage").on("change", function () {
            monitoringTable.page.len($(this).val()).draw();
          });
        },
      });

      return monitoringTable;
    } catch (error) {
      console.error("Error initializing monitoring table:", error);
      return null;
    }
  }

  function setupEventHandlers() {
    // Remove existing handlers
    $(window).off("resize.monitoring");
    $("#monitoringTable").off("click", ".btn-view");
    $("#confirmImportBtn").off("click");
    $("#downloadTemplateBtn").off("click");
    $("#exportMonitoringBtn").off("click");
    $("#monitoringSearch").off("keyup");
    $("#monitoringPerPage").off("change");

    // Add new handlers
    $(window).on("resize.monitoring", function () {
      if (monitoringTable) {
        monitoringTable.columns.adjust();
      }
    });

    $("#monitoringTable").on("click", ".btn-view", function () {
      handleViewDetails($(this));
    });

    $("#confirmImportBtn").on("click", handleFileImport);
    $("#downloadTemplateBtn").on("click", function () {
      window.location.href = "../backend/download_template.php";
    });

    // Add search handler
    $("#monitoringSearch").on("keyup", function () {
      if (monitoringTable) {
        monitoringTable.search(this.value).draw();
      }
    });

    // Add length change handler
    $("#monitoringPerPage").on("change", function () {
      if (monitoringTable) {
        monitoringTable.page.len($(this).val()).draw();
      }
    });

    // Add export handler
    $("#exportMonitoringBtn").on("click", function () {
      console.log("Export button clicked - Sending request...");
      $.ajax({
        url: "../backend/export_monitoring.php",
        method: "GET",
        xhrFields: {
          responseType: "blob",
        },
        beforeSend: function () {
          console.log("Starting export request...");
        },
        success: function (response, status, xhr) {
          console.log(
            "Export response received",
            xhr.getResponseHeader("Content-Type")
          );

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
          console.log("File download initiated");
        },
        error: function (xhr, status, error) {
          console.error("Export failed - Status:", status);
          console.error("Error:", error);
          console.error("Response:", xhr.responseText);
          alert("Failed to export data. Please check the console for details.");
        },
      });
    });
  }

  function setupModalHandlers() {
    // Remove previous handlers
    $("#checkupHistoryModal, #monitoringDetailsModal, #importDataModal").off();

    // Add new handlers
    $("#checkupHistoryModal")
      .on("show.bs.modal", function () {
        console.log("History modal is showing");
      })
      .on("hidden.bs.modal", function () {
        // Show monitoring details modal after history modal is hidden
        $("#monitoringDetailsModal").modal("show");
      });

    $("#monitoringDetailsModal").on("hidden.bs.modal", function () {
      currentPatientId = null;
      $(".modal-backdrop").remove();
      document.body.classList.remove("modal-open");
      document.body.style.removeProperty("overflow");
      document.body.style.removeProperty("padding-right");
    });

    $("#importDataModal")
      .on("show.bs.modal", function () {
        // Store the element that had focus before opening the modal
        $(this).data("returnFocus", document.activeElement);
      })
      .on("shown.bs.modal", function () {
        // Set focus to the file input when modal is shown
        $("#importFile").focus();
      })
      .on("hidden.bs.modal", function () {
        // Clean up after modal is fully hidden
        $(".modal-backdrop").remove();
        document.body.classList.remove("modal-open");
        document.body.style.removeProperty("overflow");
        document.body.style.removeProperty("padding-right");
        // Return focus to the import button that opened the modal
        $('button[data-bs-target="#importDataModal"]').focus();
      });
  }

  function adjustTable() {
    if (monitoringTable) {
      monitoringTable.columns.adjust().draw();
    }
  }

  return {
    init: function () {
      if (!isInitialized) {
        initializeToastStyles();
        initializeTable();
        setupEventHandlers();
        setupModalHandlers();
        isInitialized = true;
      }
    },
    getTable: function () {
      return monitoringTable;
    },
    refreshTable: function () {
      if (monitoringTable) {
        monitoringTable.ajax.reload(null, false);
      }
    },
    destroy: function () {
      if (monitoringTable) {
        monitoringTable.destroy();
        monitoringTable = null;
      }
      isInitialized = false;
    },
    adjustTable: adjustTable,
  };
})();

// Handle tab events
$(document).ready(function () {
  // Initialize when monitoring tab is shown
  $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
    if ($(e.target).attr("id") === "schedule-tab") {
      MonitoringModule.init();
    }
  });

  // Cleanup when leaving monitoring tab
  $('button[data-bs-toggle="tab"]').on("hide.bs.tab", function (e) {
    if ($(e.target).attr("id") === "schedule-tab") {
      MonitoringModule.destroy();
    }
  });
});
