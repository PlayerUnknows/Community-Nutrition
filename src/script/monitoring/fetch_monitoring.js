// Global variable for the monitoring table
window.monitoringTable = null;

// Make initializeTable globally accessible
window.initializeTable = function() {
    try {
      // Always destroy existing instance first
      if (window.monitoringTable && typeof window.monitoringTable.destroy === 'function') {
        window.monitoringTable.destroy();
        window.monitoringTable = null;
        $("#monitoringTable tbody").empty();
      }

      // Create new instance
      window.monitoringTable = $("#monitoringTable").DataTable({
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
                '<button class="btn btn-primary btn-sm btn-view" data-checkup-id="' +
                row.checkup_unique_id +
                '"><i class="fas fa-eye"></i> View</button>'
              );
            },
          },
        ],
        ajax: {
          url: "../controllers/MonitoringController.php?action=fetchAllMonitoring",
          method: "GET",
          dataSrc: function (json) {
            return json.status === "success" ? json.data : [];
          },
        },
        order: [[0, "desc"]],
        initComplete: function () {
          // Event handlers are set up in the main init function
          this.api().columns.adjust();

          // Setup custom search
          $("#monitoringSearch").on("keyup", function () {
            if (window.monitoringTable) {
              window.monitoringTable.search(this.value).draw();
            }
          });

          // Setup custom length menu
          $("#monitoringPerPage").on("change", function () {
            if (window.monitoringTable) {
              window.monitoringTable.page.len($(this).val()).draw();
            }
          });
        },
      });

      return window.monitoringTable;
    } catch (error) {
      console.error("Error initializing monitoring table:", error);
      return null;
    }
  };




