<?php
require_once __DIR__ . '/../controllers/MonitoringController.php';

header('Content-Type: application/json');

$controller = new MonitoringController();
$controller->fetchAllMonitoring();
