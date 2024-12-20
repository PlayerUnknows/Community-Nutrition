<!-- /app/views/user/home.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Community Nutrition Information System</title>
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/parent.css"> <!-- Custom Styles -->
</head>
<body>
    <div class="container">
        <h1>Welcome to the Community Nutrition Information System!</h1>
        <p>This system will help improve the community's nutrition and health management.</p>
        <p>Here you can manage appointments, track activities, and get personalized nutrition advice.</p>
        <!-- Add more content as necessary -->
        <a href="../../index.php" class="btn btn-danger logout-button">Logout</a>
    </div>

      <!-- Include jQuery library -->
      <script src="../../node_modules/jquery/dist/jquery.js"></script>
    <!-- Include Popper.js for Bootstrap tooltips-->
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.js"></script>
    <!-- Include Bootstrap JavaScript -->
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Include SweetAlert JavaScript -->
    <script src="../../node_modules/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Link to your separate JS file -->
    <script src="/src/script/logout.js"></script>
</body>
</html>
