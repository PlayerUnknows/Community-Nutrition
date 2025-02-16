console.log('Patient Profile JS loaded');

$(document).ready(function() {
    console.log('Initializing DataTable...');
    
    // Remove any existing DataTable instance
    if ($.fn.DataTable.isDataTable('#patientTable')) {
        $('#patientTable').DataTable().destroy();
    }
    
    var table = $('#patientTable').DataTable({
        processing: true,
        pageLength: 5, // Default display 5 entries
        lengthChange: false, // Hide the default length menu since we're using our custom one
        searching: true, // Enable searching
        search: {
            return: true, // Enable search on Enter key
            smart: true // Enable smart search
        },
        scrollX: true, // Enable horizontal scrolling
        scrollY: '50vh', // Enable vertical scrolling with 50% viewport height
        scrollCollapse: true, // Enable scroll collapse
        ajax: {
            url: '../controllers/patient_controller.php?action=getAllPatients',
            dataSrc: function(json) {
                console.log('Server response:', json);
                if (json.status === 'success') {
                    return json.data;
                } else {
                    console.error('Server error:', json.message);
                    return [];
                }
            },
            error: function(xhr, error, thrown) {
                console.error('Ajax error:', error, thrown);
                alert('Error loading data. Please check the console for details.');
            }
        },
        columns: [
            { 
                data: 'patient_fam_id',
                className: 'font-monospace'
            },
            { 
                data: 'patient_id',
                className: 'font-monospace'
            },
            { 
                data: 'patient_fname',
                className: 'text-start'
            },
            { 
                data: 'patient_lname',
                className: 'text-start'
            },
            { 
                data: 'age',
                className: 'text-center'
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-info view-patient" data-id="' + row.patient_id + '">' +
                           '<i class="fas fa-eye"></i> View</button>';
                }
            }
        ],
        order: [[0, 'asc']],
        dom: 't<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>', // Only show table, info and pagination
        language: {
            emptyTable: 'No patients found',
            loadingRecords: 'Loading...',
            processing: 'Processing...',
            zeroRecords: 'No matching records found',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            search: '',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        }
    });

    // Handle search input
    $('#patientSearch').on('input', function() {
        console.log('Search input:', this.value);
        table.search(this.value).draw();
    });

    // Handle entries per page change
    $('#patientPerPage').on('change', function() {
        console.log('Entries per page:', $(this).val());
        table.page.len(parseInt($(this).val())).draw();
    });

    // Handle view patient details
    $('#patientTable').on('click', '.view-patient', function() {
        var patientId = $(this).data('id');
        console.log('View patient:', patientId);
        
        $.ajax({
            url: '../controllers/patient_controller.php',
            method: 'GET',
            data: {
                action: 'getPatientDetails',
                patient_id: patientId
            },
            success: function(response) {
                console.log('Patient details response:', response);
                if (response.status === 'success' && response.data) {
                    var patient = response.data;
                    var detailsHtml = `
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <h5>${patient.patient_fname} ${patient.patient_lname}</h5>
                                    <hr>
                                    <p><strong>ID:</strong> ${patient.patient_id}</p>
                                    <p><strong>Family ID:</strong> ${patient.patient_fam_id}</p>
                                    <p><strong>Age:</strong> ${patient.age}</p>
                                    <p><strong>Sex:</strong> ${patient.sex}</p>
                                    <p><strong>Birth Date:</strong> ${patient.date_of_birth}</p>
                                    <p><strong>Food Restrictions:</strong> ${patient.patient_food_restrictions || 'None'}</p>
                                    <p><strong>Medical History:</strong> ${patient.patient_medical_history || 'None'}</p>
                                    <p><strong>Dietary Record:</strong> ${patient.dietary_consumption_record || 'None'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#patientDetailsModal .modal-body').html(detailsHtml);
                    $('#patientDetailsModal').modal('show');
                } else {
                    console.error('Failed to load patient details:', response.message);
                    alert('Failed to load patient details. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading patient details:', error);
                alert('Failed to load patient details. Please try again.');
            }
        });
    });

    // Handle window resize
    $(window).on('resize', function() {
        table.columns.adjust().draw();
    });
});
