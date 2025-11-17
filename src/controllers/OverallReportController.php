<?php
require_once __DIR__ . '/../models/OptModel.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';
    $model = new OptModel();

    if ($action === 'getOPTOverallReport') {
        $data = $model->getOPTOverallReport();
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else if ($action === 'getUnifiedOPTOverallReport') {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $data = $model->getUnifiedOPTOverallReport($startDate, $endDate);
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else if ($action === 'getBMIDetails') {
        $data = $model->getBMIDetails();
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else if ($action === 'getLocationStatistics') {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $data = $model->getLocationStatistics($startDate, $endDate);
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 