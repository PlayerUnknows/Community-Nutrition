$(document).ready(function () {
  let currentPage = 1;
  let itemsPerPage = parseInt($("#usersPerPage").val()) || 10;
  let allUsers = [];

  // Initialize toast
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  });

  // Handle sub-navigation for Create Account tab
  $(".sub-nav-button").click(function () {
    const target = $(this).data("target");
    $(".sub-content").hide();
    $(`#${target}`).show();

    // Initialize users table when switching to users view
    if (target === "view-users") {
      loadUsers();
    }
  });

  function loadUsers() {
    // Show loading toast
    Toast.fire({
      icon: 'info',
      title: 'Loading users...'
    });

    $.ajax({
      url: "../backend/fetch_users.php",
      type: "POST",
      data: {
        page: currentPage,
        length: itemsPerPage,
        search: $("#usersSearch").val(),
      },
      success: function (response) {
        if (!response || !response.data) {
          Toast.fire({
            icon: 'error',
            title: 'No data available'
          });
          return;
        }
        allUsers = response.data;
        updateTable();
        Toast.fire({
          icon: 'success',
          title: 'Users loaded successfully'
        });
      },
      error: function (xhr, status, error) {
        console.error("Error loading users:", error);
        Toast.fire({
          icon: 'error',
          title: 'Failed to load users'
        });
      },
    });
  }

  function updateTable(filteredData = null) {
    const table = $("#usersTable tbody");
    table.empty();

    const dataToUse = filteredData || allUsers;
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedData = dataToUse.slice(startIndex, endIndex);

    paginatedData.forEach(function (user) {
      const row = `
                <tr>
                    <td class="text-center">${user.user_id}</td>
                    <td>${user.email}</td>
                    <td>
                        <span class="badge bg-${
                          user.role === "Administrator"
                            ? "primary"
                            : user.role === "Brgy Health Worker"
                            ? "success"
                            : "info"
                        }">${user.role}</span>
                    </td>
                    <td>${user.created_at}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-info view-user" data-id="${
                              user.user_id
                            }" title="View">
                                <i class="far fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary edit-user" data-id="${
                              user.user_id
                            }" title="Edit">
                                <i class="far fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-user" data-id="${
                              user.user_id
                            }" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
      table.append(row);
    });

    updatePagination(dataToUse.length);
  }

  function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const showing = `Showing ${(currentPage - 1) * itemsPerPage + 1}-${Math.min(
      currentPage * itemsPerPage,
      totalItems
    )} of ${totalItems} entries`;
    $("#users-showing-entries").text(showing);

    // Update page numbers
    const pageNumbers = $(".users-page-numbers");
    pageNumbers.empty();

    // Calculate range of page numbers to show
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);

    // Adjust start if we're near the end
    if (endPage - startPage < 4) {
      startPage = Math.max(1, endPage - 4);
    }

    // Add first page if not in range
    if (startPage > 1) {
      pageNumbers.append(`
                <a class="page-link" href="#" data-page="1">1</a>
                ${startPage > 2 ? '<span class="page-link">...</span>' : ""}
            `);
    }

    // Add page numbers
    for (let i = startPage; i <= endPage; i++) {
      pageNumbers.append(`
                <a class="page-link ${i === currentPage ? "active" : ""}" 
                   href="#" 
                   data-page="${i}">${i}</a>
            `);
    }

    // Add last page if not in range
    if (endPage < totalPages) {
      pageNumbers.append(`
                ${
                  endPage < totalPages - 1
                    ? '<span class="page-link">...</span>'
                    : ""
                }
                <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
            `);
    }

    // Update prev/next buttons
    $("#usersPrevPage")
      .parent()
      .toggleClass("disabled", currentPage === 1);
    $("#usersNextPage")
      .parent()
      .toggleClass("disabled", currentPage === totalPages);
  }

  // Event Handlers
  $("#usersPerPage").change(function () {
    itemsPerPage = parseInt($(this).val());
    currentPage = 1;
    updateTable();
  });

  $("#usersSearch").on("input", function () {
    const searchTerm = $(this).val().toLowerCase();
    const filteredData = allUsers.filter(
      (user) =>
        user.user_id.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm) ||
        user.role.toLowerCase().includes(searchTerm) ||
        user.created_at.toLowerCase().includes(searchTerm)
    );
    currentPage = 1;
    updateTable(filteredData);
  });

  // Pagination click handlers
  $("#usersPagination").on("click", ".page-link", function (e) {
    e.preventDefault();
    const page = $(this).data("page");
    if (page && page !== currentPage) {
      currentPage = page;
      updateTable();
    }
  });

  $("#usersPrevPage").click(function (e) {
    e.preventDefault();
    if (currentPage > 1) {
      currentPage--;
      updateTable();
    }
  });

  $("#usersNextPage").click(function (e) {
    e.preventDefault();
    const totalPages = Math.ceil(allUsers.length / itemsPerPage);
    if (currentPage < totalPages) {
      currentPage++;
      updateTable();
    }
  });

  // Handle View User
  $("#usersTable").on("click", ".view-user", function () {
    const userId = $(this).data("id");
    $.ajax({
      url: "../backend/fetch_single_user.php",
      type: "POST",
      data: { user_id: userId },
      success: function (response) {
        if (response.success) {
          const user = response.data;
          Swal.fire({
            title: "User Details",
            html: `
                            <div class="text-start">
                                <p><strong>User ID:</strong> ${user.user_id}</p>
                                <p><strong>Name:</strong> ${user.full_name}</p>
                                <p><strong>Role:</strong> ${user.role}</p>
                                <p><strong>Created At:</strong> ${user.created_at}</p>
                            </div>
                        `,
            icon: "info",
          });
        } else {
          Swal.fire("Error", "Failed to load user details", "error");
        }
      },
      error: function () {
        Swal.fire("Error", "Failed to load user details", "error");
      },
    });
  });

  // Handle Delete User with improved UI
  $("#usersTable").on("click", ".delete-user", function () {
    const userId = $(this).data("id");
    const deleteButton = $(this);
    const originalHtml = deleteButton.html();

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash me-1"></i>Yes, delete it!',
        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state in button
            deleteButton.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Deleting...');
            deleteButton.prop('disabled', true);

            setTimeout(() => {
                $.ajax({
                    url: "../backend/delete_user.php",
                    type: "POST",
                    data: { user_id: userId },
                    success: function(response) {
                        if (response.success) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer)
                                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                                }
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'Successfully deleted!'
                            }).then(() => {
                                loadUsers(); // Refresh the users table
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Failed to delete user'
                            });
                        }
                    },
                    error: function() {
                        Toast.fire({
                            icon: 'error',
                            title: 'Failed to connect to the server'
                        });
                    },
                    complete: function() {
                        // Restore button state
                        deleteButton.html(originalHtml);
                        deleteButton.prop('disabled', false);
                    }
                });
            }, 1000); // 1 second minimum loading time
        }
    });
  });

  // Handle Edit User with improved UI
  $("#usersTable").on("click", ".edit-user", function () {
    const userId = $(this).data("id");
    const editButton = $(this);
    const originalHtml = editButton.html();

    // Show loading in button
    editButton.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
    editButton.prop('disabled', true);

    setTimeout(() => {
        $.ajax({
            url: "../backend/fetch_single_user.php",
            type: "POST",
            data: { user_id: userId },
            success: function (response) {
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
                                        <option value="Brgy Health Worker">Brgy Health Worker</option>
                                        <option value="Administrator">Administrator</option>
                                    </select>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-save me-1"></i>Save Changes',
                        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
                        preConfirm: () => {
                            const saveBtn = Swal.getConfirmButton();
                            const originalSaveBtnText = saveBtn.innerHTML;
                            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...';
                            saveBtn.disabled = true;

                            return new Promise((resolve) => {
                                setTimeout(() => {
                                    const email = $("#editEmail").val();
                                    const role = $("#editRole").val();
                                    const userId = $("#editUserId").val();

                                    $.ajax({
                                        url: "../backend/edit_user.php",
                                        type: "POST",
                                        data: { user_id: userId, email, role },
                                        dataType: "json"
                                    }).then(response => {
                                        if (!response.success) {
                                            throw new Error(response.message || 'Failed to update user');
                                        }
                                        return response;
                                    }).then(resolve)
                                    .catch(error => {
                                        Swal.showValidationMessage(error.message);
                                        saveBtn.innerHTML = originalSaveBtnText;
                                        saveBtn.disabled = false;
                                    });
                                }, 1000);
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'Successfully updated!'
                            }).then(() => {
                                loadUsers();
                            });
                        }
                    });

                    // Set the correct role option
                    $("#editRole").val(user.role);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Failed to fetch user details'
                    });
                }
            },
            error: function () {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to fetch user details'
                });
            },
            complete: function() {
                // Restore button state
                editButton.html(originalHtml);
                editButton.prop('disabled', false);
            }
        });
    }, 1000); // 1 second minimum loading time
  });
});
