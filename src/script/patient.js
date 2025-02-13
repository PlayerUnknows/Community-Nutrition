$(document).ready(function() {
    const patientTable = new DataTable('#patientTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: '../backend/fetch_patients.php',
            method: 'POST'
        },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'age' },
            { data: 'contact' },
            { data: 'family_record' },
            { data: 'medical_history' },
            { data: 'restrictions' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary edit-patient" data-id="${row.id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-patient" data-id="${row.id}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    `;
                }
            }
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
    });
});