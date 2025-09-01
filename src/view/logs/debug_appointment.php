<?php
require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../models/Appointment.php';

echo "<h1>🔍 Debug Appointment Service</h1>";

// Test with a specific ID
$testId = isset($_GET['id']) ? $_GET['id'] : 54;

echo "<h3>Testing Appointment ID: $testId</h3>";

try {
    echo "<h4>Step 1: Testing Appointment Model</h4>";
    $appointment = new Appointment();
    echo "✅ Appointment model created<br>";
    
    echo "<h4>Step 2: Testing getAppointmentById()</h4>";
    $result = $appointment->getAppointmentById($testId);
    echo "✅ getAppointmentById() executed<br>";
    
    echo "<h4>Step 3: Fetching Data</h4>";
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "✅ Appointment found!<br>";
        echo "<pre>";
        print_r($row);
        echo "</pre>";
        
        Logger::info("Debug: Appointment found", [
            'id' => $testId,
            'data' => $row
        ]);
    } else {
        echo "❌ No appointment found with ID: $testId<br>";
        Logger::warning("Debug: No appointment found", [
            'id' => $testId
        ]);
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    Logger::error("Debug: Exception in appointment test", [
        'error' => $e->getMessage(),
        'id' => $testId
    ]);
}

echo "<br><hr><br>";
echo "<h2>📊 Test Results</h2>";
echo "<p>Check the logs to see detailed results:</p>";
echo "<a href='view_logs.php' style='background: #007acc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 View Logs</a>";
echo "<br><br>";
echo "<p><strong>Test URL:</strong> <code>debug_appointment.php?id=54</code></p>";
?>
