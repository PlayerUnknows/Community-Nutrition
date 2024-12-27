<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Community Nutrition System</title>
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../node_modules/@fortawesome/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css">
    <link rel="stylesheet" href="../../node_modules/datatables.net-bs5/css/dataTables.bootstrap5.css">

    <!--core js-->
    <script src="../../node_modules/jquery/dist/jquery.js"></script>
    <script src="../../node_modules/datatables.net/js/jquery.dataTables.js"></script>
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.js"></script>
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../node_modules/sweetalert2/dist/sweetalert2.js"></script>
   
    <script src="../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.js"></script>

    <style>
        .text-right {
            text-align: right;
        }
        
        /* DataTable styling */
        .dataTables_wrapper .dataTables_length {
            margin-right: 2rem;
            display: inline-block;
        }
        
        .dataTables_wrapper .dataTables_filter {
            display: inline-block;
            float: right;
        }
        
        .dataTables_wrapper .dataTables_info {
            padding-top: 0.85em;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.5em;
            float: right;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.3em 0.8em;
            margin-left: 2px;
            cursor: pointer;
        }
        
        .dataTables_wrapper .row {
            margin-bottom: 1rem;
        }

        /* Added styles for centering and layout */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 1rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin: 1rem 0;
            color: #333;
        }

        #usersTable {
            margin: 0;
            width: 100%;
        }

        #usersTable th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .table-responsive {
            padding: 1rem;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <h1 class="page-title">User Management</h1>
        <div class="table-container">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-right">User ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script src="../script/users.js"></script>
</body>

</html>