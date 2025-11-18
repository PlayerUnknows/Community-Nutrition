// Simple Audit Trail DataTable
$(document).ready(function() {
  $("#auditTable").DataTable({
    ajax: {
      url: "../backend/fetch_audit_trail.php",
      dataSrc: "data"
    },
    columns: [
      { data: "username", defaultContent: "System" },
      { data: "action" },
      { data: "details" },
      { 
        data: "action_timestamp",
        render: function(data) {
          return data ? moment(data).format("YYYY-MM-DD HH:mm:ss") : "N/A";
        }
      }
    ],
    order: [[3, "desc"]],
    pageLength: 10,
    responsive: true,
    autoWidth: false
  });
});
