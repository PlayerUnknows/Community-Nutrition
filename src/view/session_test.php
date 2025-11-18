<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Test - Community Nutrition System</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <style>
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
        }

        .session-info {
            margin-top: 20px;
        }

        .card {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Session Test Page</h1>
        <p class="lead">Use this page to test and debug session functionality</p>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Session Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <button id="btnCheckSession" class="btn btn-info">Check Session</button>
                            <button id="btnResetSession" class="btn btn-warning">Reset Session Timer</button>
                            <button id="btnDestroySession" class="btn btn-danger">Destroy Session</button>
                            <button id="btnTestData" class="btn btn-secondary">Set Test Data</button>
                            <a href="../../index.php" class="btn btn-primary">Go to Login Page</a>
                            <a href="admin.php" class="btn btn-success">Go to Admin Dashboard</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        Current PHP Session Data
                    </div>
                    <div class="card-body">
                        <pre><?php print_r($_SESSION); ?></pre>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        Session Debug Info
                    </div>
                    <div class="card-body">
                        <pre id="sessionInfo">Click "Check Session" to see current session data</pre>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-dark text-white">
                        Response Log
                    </div>
                    <div class="card-body">
                        <pre id="responseLog"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery -->
    <script src="../../node_modules/jquery/dist/jquery.js"></script>
    <!-- Include Popper.js -->
    <script src="../../node_modules/@popperjs/core/dist/umd/popper.js"></script>
    <!-- Include Bootstrap JS -->
    <script src="../../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            const debugEndpoint = '../../src/backend/session_debug.php';
            const logResponse = (action, response) => {
                const timestamp = new Date().toLocaleTimeString();
                const logEntry = `[${timestamp}] ${action}:\n${JSON.stringify(response, null, 2)}\n\n`;

                // Prepend to log (newest at top)
                $('#responseLog').prepend(logEntry);
            };

            // Check Session
            $('#btnCheckSession').click(function() {
                $.ajax({
                    url: debugEndpoint,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#sessionInfo').text(JSON.stringify(response.session_data, null, 2));
                        logResponse('Check Session', response);
                    },
                    error: function(xhr, status, error) {
                        logResponse('Error', {
                            status,
                            error,
                            responseText: xhr.responseText
                        });
                    }
                });
            });

            // Reset Session
            $('#btnResetSession').click(function() {
                $.ajax({
                    url: `${debugEndpoint}?action=reset_session`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#sessionInfo').text(JSON.stringify(response.session_data, null, 2));
                        logResponse('Reset Session', response);
                    },
                    error: function(xhr, status, error) {
                        logResponse('Error', {
                            status,
                            error,
                            responseText: xhr.responseText
                        });
                    }
                });
            });

            // Destroy Session
            $('#btnDestroySession').click(function() {
                if (confirm('Are you sure you want to destroy the session?')) {
                    $.ajax({
                        url: `${debugEndpoint}?action=destroy_session`,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $('#sessionInfo').text('Session destroyed. Refresh page to see changes.');
                            logResponse('Destroy Session', response);
                        },
                        error: function(xhr, status, error) {
                            logResponse('Error', {
                                status,
                                error,
                                responseText: xhr.responseText
                            });
                        }
                    });
                }
            });

            // Set Test Data
            $('#btnTestData').click(function() {
                $.ajax({
                    url: `${debugEndpoint}?action=set_test_data`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#sessionInfo').text(JSON.stringify(response.session_data, null, 2));
                        logResponse('Set Test Data', response);
                    },
                    error: function(xhr, status, error) {
                        logResponse('Error', {
                            status,
                            error,
                            responseText: xhr.responseText
                        });
                    }
                });
            });

            // Initial session check
            $('#btnCheckSession').click();
        });
    </script>
</body>

</html>