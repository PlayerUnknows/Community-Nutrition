$(document).ready(function() {
    // Initialize DataTable with proper path to controller
    var appointmentsTable = $('#upcomingTable').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "/src/controllers/AppointmentController.php?action=getUpcoming",
            "type": "GET",
            "dataSrc": function(json) {
                if (!json) return [];
                return json;
            }
        },
        "columns": [
            { "data": "patient_id" },
            { "data": "name" },
            { "data": "age" },
            { "data": "accompanied_by" },
            { 
                "data": "date",
                "render": function(data) {
                    return moment(data).format('YYYY-MM-DD');
                }
            },
            { "data": "time" }
        ],
        "order": [[4, "asc"], [5, "asc"]],
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]],
        "responsive": true,
        "language": {
            "emptyTable": "No upcoming appointments found",
            "zeroRecords": "No matching appointments found",
            "processing": "Loading appointments..."
        },
        "dom": '<"top"lf>rt<"bottom"ip><"clear">'
    });

    // Custom search functionality
    $('#upcomingSearch').on('keyup', function() {
        appointmentsTable.search($(this).val()).draw();
    });

    // Refresh table every 5 minutes
    setInterval(function() {
        appointmentsTable.ajax.reload(null, false);
    }, 300000);
});
