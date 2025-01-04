console.log('Monitoring.js loaded');
$(document).ready(function() {
    console.log('Initializing monitoring table...');
    
    // Initialize DataTable
    const monitoringTable = $('#monitoringTable').DataTable({
        ajax: {
            url: '../backend/fetch_monitoring.php',
            dataSrc: function(json) {
                console.log('Received data:', json);
                if (!json || !json.data) {
                    console.error('Invalid response format:', json);
                    return [];
                }
                return json.data;
            }
        },
        scrollX: true,
        scrollY: '50vh',
        scrollCollapse: true,
        autoWidth: false,
        fixedHeader: {
            header: true,
            headerOffset: 0
        },
        columns: [
            { data: 'patient_id', width: '100px' },
            { data: 'patient_fam_id', width: '100px' },
            { data: 'age', width: '80px' },
            { data: 'sex', width: '80px' },
            { data: 'weight', width: '100px' },
            { data: 'height', width: '100px' },
            { data: 'bp', width: '100px' },
            { data: 'temperature', width: '100px' },
            { data: 'weight_category', width: '120px' },
            { data: 'finding_bmi', width: '120px' },
            { data: 'finding_growth', width: '120px' },
            { data: 'arm_circumference', width: '120px' },
            { data: 'arm_circumference_status', width: '120px' },
            { data: 'findings', width: '150px' },
            { 
                data: 'date_of_appointment',
                width: '150px',
                render: function(data) {
                    return data ? moment(data).format('MMMM D, YYYY') : '';
                }
            },
            { data: 'time_of_appointment', width: '120px' },
            { data: 'place', width: '150px' },
            { 
                data: 'created_at',
                width: '180px',
                render: function(data) {
                    return data ? moment(data).format('MMMM D, YYYY h:mm A') : '';
                }
            },
            {
                data: null,
                width: '100px',
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-info view-monitoring" data-id="${data.checkup_prikey}">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[17, 'desc']], // Sort by created_at by default
        dom: 't', // Only show the table
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        pageLength: 10,
        language: {
            processing: "Loading...",
            emptyTable: "No monitoring records found",
            zeroRecords: "No matching records found"
        }
    });
    
    // Custom length change
    $('#monitoringLength').on('change', function() {
        monitoringTable.page.len($(this).val()).draw();
        updatePagination();
    });

    // Custom search
    $('#monitoringSearch').on('keyup', function() {
        monitoringTable.search(this.value).draw();
        updatePagination();
    });

    // Update pagination and info
    function updatePagination() {
        const info = monitoringTable.page.info();
        $('#monitoringInfo').html(
            'Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries'
        );

        const $pagination = $('#monitoringPagination .pagination');
        $pagination.empty();

        // First page
        $pagination.append(`
            <li class="page-item ${info.page === 0 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="first"><i class="fas fa-angle-double-left"></i></a>
            </li>
        `);

        // Previous page
        $pagination.append(`
            <li class="page-item ${info.page === 0 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="previous"><i class="fas fa-angle-left"></i></a>
            </li>
        `);

        // Page numbers
        let startPage = Math.max(0, info.page - 2);
        let endPage = Math.min(info.pages - 1, info.page + 2);

        for (let i = startPage; i <= endPage; i++) {
            $pagination.append(`
                <li class="page-item ${info.page === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i + 1}</a>
                </li>
            `);
        }

        // Next page
        $pagination.append(`
            <li class="page-item ${info.page === info.pages - 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="next"><i class="fas fa-angle-right"></i></a>
            </li>
        `);

        // Last page
        $pagination.append(`
            <li class="page-item ${info.page === info.pages - 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="last"><i class="fas fa-angle-double-right"></i></a>
            </li>
        `);
    }

    // Handle pagination clicks
    $('#monitoringPagination').on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        
        switch(page) {
            case 'first':
                monitoringTable.page('first').draw(false);
                break;
            case 'previous':
                monitoringTable.page('previous').draw(false);
                break;
            case 'next':
                monitoringTable.page('next').draw(false);
                break;
            case 'last':
                monitoringTable.page('last').draw(false);
                break;
            default:
                monitoringTable.page(parseInt(page)).draw(false);
        }
        
        updatePagination();
    });

    // Initial pagination setup
    monitoringTable.on('draw', function() {
        updatePagination();
    });

    // View Monitoring Details
    $('#monitoringTable').on('click', '.view-monitoring', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '../backend/get_monitoring_details.php',
            method: 'GET',
            data: { id: id },
            success: function(response) {
                if (!response.data) {
                    console.error('Invalid response:', response);
                    return;
                }

                const data = response.data;
                $('#view-patient-id').text(data.patient_id || '');
                $('#view-family-id').text(data.patient_fam_id || '');
                $('#view-age').text(data.age || '');
                $('#view-sex').text(data.sex || '');
                $('#view-weight').text(data.weight || '');
                $('#view-height').text(data.height || '');
                $('#view-bp').text(data.bp || '');
                $('#view-temperature').text(data.temperature || '');
                $('#view-weight-category').text(data.weight_category || '');
                $('#view-bmi').text(data.finding_bmi || '');
                $('#view-growth').text(data.finding_growth || '');
                $('#view-arm').text(data.arm_circumference || '');
                $('#view-arm-status').text(data.arm_circumference_status || '');
                $('#view-findings').text(data.findings || '');
                $('#view-date').text(data.date_of_appointment ? moment(data.date_of_appointment).format('MMMM D, YYYY') : '');
                $('#view-time').text(data.time_of_appointment || '');
                $('#view-place').text(data.place || '');
                $('#view-created').text(data.created_at ? moment(data.created_at).format('MMMM D, YYYY h:mm A') : '');

                $('#viewMonitoringModal').modal('show');
            },
            error: function(xhr, error) {
                console.error('AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load monitoring details'
                });
            }
        });
    });

    // Export Data
    $('#exportMonitoringBtn').click(function() {
        window.location.href = '../backend/export_monitoring.php';
    });
});
