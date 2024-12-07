<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Community Nutrition System</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/datatable.css">
    <link rel="stylesheet" href="../../assets/css/sweetalert2.css">

    <!--core js-->
    <script src="../../assets/dist/jquery.js"></script>
    <script src="../../assets/dist/datatable.js"></script>
    <script src="../../assets/dist/popper.js"></script>
    <script src="../../assets/dist/bootstrap.min.js"></script>
    <script src="../../assets/dist/sweetalert.js"></script>

    <style>
        .text-right {
            text-align: center !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <table id="usersTable" class="table table-striped">
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


    <script src="../script/users.js"></script>
</body>

</html>