<div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User Management</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="usersSearch" placeholder="Search users...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="usersPerPage">
                            <option value="5">5 per page</option>
                            <option value="10" selected>10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                      
                    </div>
                </div>

                <div class="table-container mb-3">
                    <table id="usersTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th style="min-width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Users pagination">
                    <ul class="pagination justify-content-end mb-0" id="usersPagination">
                        <li class="page-item">
                            <a class="page-link" href="#" id="usersPrevPage">Previous</a>
                        </li>
                        <li class="page-item users-page-numbers">
                            <!-- Page numbers will be inserted here -->
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#" id="usersNextPage">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <style>
        /* Table styling */
        .table-container {
            margin-top: 1rem;
            overflow-x: auto;
        }

        #usersTable {
            width: 100%;
            margin-bottom: 0;
        }

        #usersTable th,
        #usersTable td {
            vertical-align: middle;
        }

        .users-page-numbers {
            display: flex;
            margin: 0;
        }

        .users-page-numbers .page-link {
            margin: 0 2px;
        }

        .users-page-numbers .page-link.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .users-page-numbers span.page-link {
            background: none;
            border: none;
        }
    </style>
