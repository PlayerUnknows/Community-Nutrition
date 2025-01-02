$(document).ready(function() {
    // Initialize DataTable
    const monitoringTable = $('#monitoringTable').DataTable({
        ajax: {
            url: '/src/backend/fetch_monitoring.php',
            dataSrc: function(json) {
                return json.data || [];
            }
        },
        columns: [
            { data: 'patient_id' },
            { data: 'patient_fam_id' },
            { data: 'age' },
            { data: 'sex' },
            { data: 'weight' },
            { data: 'height' },
            { data: 'bp' },
            { data: 'temperature' },
            { data: 'weight_category' },
            { data: 'finding_bmi' },
            { data: 'finding_growth' },
            { data: 'arm_circumference' },
            { data: 'arm_circumference_status' },
            { data: 'findings' },
            { 
                data: 'date_of_appointment',
                render: function(data) {
                    return moment(data).format('MMMM D, YYYY');
                }
            },
            { data: 'time_of_appointment' },
            { data: 'place' },
            { 
                data: 'created_at',
                render: function(data) {
                    return moment(data).format('MMMM D, YYYY h:mm A');
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-info view-monitoring" data-id="${data.patient_id}">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[17, 'desc']], // Sort by created_at by default
        responsive: true,
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // View Monitoring Details
    $('#monitoringTable').on('click', '.view-monitoring', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '/src/backend/get_monitoring_details.php',
            method: 'GET',
            data: { id: id },
            success: function(response) {
                const data = JSON.parse(response).data;
                
                // Fill modal with data
                $('#view-patient-id').text(data.patient_id);
                $('#view-family-id').text(data.patient_fam_id);
                $('#view-age').text(data.age);
                $('#view-sex').text(data.sex);
                $('#view-weight').text(data.weight);
                $('#view-height').text(data.height);
                $('#view-bp').text(data.bp);
                $('#view-temperature').text(data.temperature);
                $('#view-weight-category').text(data.weight_category);
                $('#view-bmi').text(data.finding_bmi);
                $('#view-growth').text(data.finding_growth);
                $('#view-arm').text(data.arm_circumference);
                $('#view-arm-status').text(data.arm_circumference_status);
                $('#view-findings').text(data.findings);
                $('#view-date').text(moment(data.date_of_appointment).format('MMMM D, YYYY'));
                $('#view-time').text(data.time_of_appointment);
                $('#view-place').text(data.place);
                $('#view-created').text(moment(data.created_at).format('MMMM D, YYYY h:mm A'));

                $('#viewMonitoringModal').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch monitoring details'
                });
            }
        });
    });

    // Export Data
    $('#exportMonitoringBtn').click(function() {
        window.location.href = '/src/backend/export_monitoring.php';
    });

    // Refresh table periodically (every 5 minutes)
    setInterval(function() {
        monitoringTable.ajax.reload(null, false);
    }, 300000);
});
