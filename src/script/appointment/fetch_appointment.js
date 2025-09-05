// Fetch Appointments Manager
var fetchAppointmentManager = {
  init: function () {
    // First destroy any existing DataTable instance
    if ($.fn.DataTable.isDataTable("#appointmentsTable")) {
      $("#appointmentsTable").DataTable().destroy();
    }

    // Clear the table body
    $("#appointmentsTable tbody").empty();

    const table = $("#appointmentsTable").DataTable({
      processing: true,
      serverSide: false,
      // Remove responsive option for older DataTables compatibility
      ajax: {
        url: "/src/controllers/AppointmentController.php",
        type: "GET",
        data: {
          action: "getAll",
        },
        dataSrc: function (response) {
          if (!response || !response.data) {
            return [];
          }
          return response.data;
        },
        error: function (xhr, error, thrown) {
          return [];
        },
      },
      columns: [
        { data: "user_id", defaultContent: "" },
        { data: "patient_name", defaultContent: "" },
        { data: "date", defaultContent: "" },
        { data: "time", defaultContent: "" },
        { data: "description", defaultContent: "" },
        { data: "guardian", defaultContent: "" },
        {
          data: null,
          defaultContent: "",
          render: function (data, type, row) {
            if (!row || !row.appointment_prikey) return "";
            const appointmentId = row.appointment_prikey;
            const isCancelled = row.status === "cancelled";
            return `
              <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${appointmentId}" ${
              isCancelled ? "disabled" : ""
            }>
                <i class="fas fa-edit"></i> Edit
              </button>
              <button class="btn btn-sm ${
                isCancelled ? "btn-outline-secondary" : "btn-outline-warning"
              } cancel-btn" 
                data-id="${appointmentId}"
                ${isCancelled ? "disabled" : ""}>
                <i class="fas ${
                  isCancelled ? "fa-ban" : "fa-times"
                }"></i> ${isCancelled ? "Cancelled" : "Cancel"}
              </button>
            `;
          },
        },
      ],
      order: [[2, "desc"]], // Order by date column (index 2) descending
      pageLength: 5,
      lengthMenu: [
        [5, 10, 25, 50],
        [5, 10, 25, 50],
      ],
      language: {
        emptyTable: "No appointments found",
        zeroRecords: "No matching appointments found",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "Showing 0 to 0 of 0 entries",
        infoFiltered: "(filtered from _MAX_ total entries)",
      },
    });

    // Handle search input with sanitization
    $("#appointmentSearch").on("keyup", function (e) {
      e.preventDefault();
      const searchValue = $(this).val();
      // Use a timeout to prevent too many searches while typing
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        table.search(searchValue).draw();
      }, 300);
    });

    // Handle length change
    $("#appointmentsPerPage").on("change", function () {
      table.page.len($(this).val()).draw();
    });

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


    // Store table reference for external access
    window.appointmentsTable = table;

    return table;
  },

  // Method to refresh the table
  refresh: function () {
    return this.init();
  },
};



// Export for external use
window.fetchAppointmentManager = fetchAppointmentManager;