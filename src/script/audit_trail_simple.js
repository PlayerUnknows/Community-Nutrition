// Simple Audit Trail DataTable
$(document).ready(function() {
  // Initialize DataTable
  var table = $("#auditTable").DataTable({
    ajax: {
      url: "../backend/fetch_audit_trail.php",
      dataSrc: "data"
    },
    columns: [
      { data: "username", defaultContent: "System" },
      { data: "action" },
      { data: "details", defaultContent: "" },
      { 
        data: "action_timestamp",
        render: function(data) {
          return data ? moment(data).format("YYYY-MM-DD HH:mm:ss") : "N/A";
        }
      }
    ],
    order: [[3, "desc"]],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    responsive: true,
    autoWidth: false,
    searching: true,
    // Hide default search box since we have custom one
    dom: '<"row"<"col-sm-12"tr>>' +
         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    language: {
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: "No entries to show",
      infoFiltered: "(filtered from _TOTAL_ total entries)",
      zeroRecords: "No matching records found",
      emptyTable: "No audit trail data available"
    }
  });

  // Connect custom search input to DataTable
  $("#auditSearch").on("keyup search", function() {
    table.search(this.value).draw();
  });

  // Connect custom length selector to DataTable
  $("#auditsPerPage").on("change", function() {
    table.page.len(parseInt(this.value)).draw();
  });
});
