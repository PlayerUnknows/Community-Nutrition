   // First destroy any existing DataTable instance
   if ($.fn.DataTable.isDataTable("#appointmentsTable")) {
    $("#appointmentsTable").DataTable().destroy();
  }

  // Clear the table body
  $("#appointmentsTable tbody").empty();

const table = $("#appointmentsTable").DataTable({
    processing: true,
    serverSide: false,
    responsive: true,
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
                          <div class="btn-group">
                              <button class="btn btn-sm btn-primary edit-btn" data-id="${appointmentId}" ${
            isCancelled ? "disabled" : ""
          }>
                                  <i class="fas fa-edit"></i> Edit
                              </button>
                              <button class="btn btn-sm ${
                                isCancelled ? "btn-secondary" : "btn-warning"
                              } cancel-btn" 
                                      data-id="${appointmentId}"
                                      ${isCancelled ? "disabled" : ""}>
                                  <i class="fas ${
                                    isCancelled ? "fa-ban" : "fa-times"
                                  }"></i> 
                                  ${isCancelled ? "Cancelled" : "Cancel"}
                              </button>
                          </div>
                      `;
        },
      },
     
    ],
    order: [[1, "desc"]], // Order by date column descending
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