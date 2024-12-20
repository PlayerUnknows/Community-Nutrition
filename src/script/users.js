$(document).ready(function() {
    // Initialize DataTable
    const usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../backend/fetch_users.php',
            type: 'POST',
            dataSrc: function(json) {
                return json.success ? json.data : [];
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, thrown);
                Swal.fire('Error', 'Failed to load users data', 'error');
            }
        },
        columns: [
            { 
                data: 'user_id',
                className: 'text-center'
            },
            { data: 'email' },
            { 
                data: 'role',
                render: function(data) {
                    return `<span class="badge bg-${data === 'Administrator' ? 'primary' : data === 'Health Worker' ? 'success' : 'info'}">${data}</span>`;
                }
            },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-info view-user" data-id="${data.user_id}">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-primary edit-user" data-id="${data.user_id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger delete-user" data-id="${data.user_id}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    `;
                }
            }
        ],
        dom: '<"row align-items-center"<"col-md-6"l><"col-md-6"f>><"row"<"col-sm-12"tr>><"row align-items-center"<"col-md-5"i><"col-md-7"p>>',
        buttons: [
            {
                extend: 'collection',
                text: '<i class="fas fa-download"></i> Export',
                buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
            }
        ],
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });

    // View User Details
    $('#usersTable').on('click', '.view-user', function() {
        const userId = $(this).data('id');
        
        $.ajax({
            url: '../backend/fetch_single_user.php',
            type: 'POST',
            data: { user_id: userId },
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    Swal.fire({
                        title: 'User Details',
                        html: `
                            <table class="table">
                                <tr><th>User ID</th><td>${user.user_id}</td></tr>
                                <tr><th>Email</th><td>${user.email}</td></tr>
                                <tr><th>Role</th><td>${user.role}</td></tr>
                                <tr><th>Created At</th><td>${user.created_at}</td></tr>
                            </table>
                        `,
                        icon: 'info',
                        confirmButtonText: 'Close'
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to fetch user details', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to fetch user details', 'error');
            }
        });
    });

    // Edit User
    $('#usersTable').on('click', '.edit-user', function() {
        const userId = $(this).data('id');
        
        $.ajax({
            url: '../backend/fetch_single_user.php',
            type: 'POST',
            data: { user_id: userId },
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    Swal.fire({
                        title: 'Edit User',
                        html: `
                            <form id="editUserForm">
                                <input type="hidden" id="editUserId" value="${user.user_id}">
                                <div class="mb-3">
                                    <label for="editEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editEmail" value="${user.email}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editRole" class="form-label">Role</label>
                                    <select class="form-control" id="editRole" required>    
                                        <option value="Parent">Parent</option>
                                        <option value="Health Worker">Health Worker</option>
                                        <option value="Administrator">Administrator</option>
                                    </select>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Save Changes',
                        preConfirm: () => {
                            const email = $('#editEmail').val();
                            const role = $('#editRole').val();
                            const userId = $('#editUserId').val();

                            return $.ajax({
                                url: '../backend/edit_user.php',
                                type: 'POST',
                                data: { 
                                    user_id: userId, 
                                    email: email, 
                                    role: role 
                                },
                                dataType: 'json'
                            }).then(response => {
                                if (!response.success) {
                                    Swal.showValidationMessage(response.message || 'Failed to update user');
                                }
                                return response;
                            }).catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`);
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire('Updated!', 'User has been updated.', 'success');
                            usersTable.ajax.reload();
                        }
                    });
                    // Populate edit modal with user data
                    $('#editUserId').val(user.user_id);
                    $('#editEmail').val(user.email);
                    // Set the correct role option as selected
                    $('#editRole').val(user.role);
                } else {
                    Swal.fire('Error', response.message || 'Failed to fetch user details', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to fetch user details', 'error');
            }
        });
    });

    // Delete User
    $('#usersTable').on('click', '.delete-user', function() {
        const userId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../backend/delete_user.php',
                    type: 'POST',
                    data: { user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message || 'User has been deleted.',
                                'success'
                            );
                            usersTable.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete user', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Failed to delete user: ' + error, 'error');
                    }
                });
            }
        });
    });
});
