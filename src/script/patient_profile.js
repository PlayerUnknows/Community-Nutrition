$(document).ready(function() {
    const patientTable = new DataTable('#patientTable', {
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        scrollY: '50vh',
        scrollCollapse: true
    });

    // Handle view button click
    $('#patientTable').on('click', '.view-patient', function() {
        const patientDetails = $(this).data('details');
        Swal.fire({
            title: '<strong>Patient Details</strong>',
            html: '<div><strong>Middle Initial:</strong> ' + patientDetails.patient_mi + '</div>' +
                  '<div><strong>Suffix:</strong> ' + patientDetails.patient_suffix + '</div>' +
                  '<div><strong>Sex:</strong> ' + patientDetails.sex + '</div>' +
                  '<div><strong>Date of Birth:</strong> ' + patientDetails.date_of_birth + '</div>' +
                  '<div><strong>Food Restrictions:</strong> ' + patientDetails.patient_food_restrictions + '</div>' +
                  '<div><strong>Medical History:</strong> ' + patientDetails.patient_medical_history + '</div>' +
                  '<div><strong>Dietary Record:</strong> ' + patientDetails.dietary_consumption_record + '</div>',
            showCloseButton: true,
            focusConfirm: false,
            confirmButtonText: 'Close'
        });
    });
});
