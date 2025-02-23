<?php
require_once __DIR__ . '/../models/ReportModel.php';

class ReportController {
    private $reportModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
    }

    public function getGrowthTrendsData($patientId = null) {
        try {
            return $this->reportModel->getGrowthTrendsData($patientId);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getNutritionalStatusSummary() {
        try {
            return $this->reportModel->getNutritionalStatusSummary();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getAgeGroupAnalysis() {
        try {
            return $this->reportModel->getAgeGroupAnalysis();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function generateReport($startDate = null, $endDate = null, $patientId = null) {
        try {
            $data = [
                'growthTrends' => $this->reportModel->getGrowthTrendsData($startDate, $endDate, $patientId),
                'nutritionalStatus' => $this->reportModel->getNutritionalStatusSummary($startDate, $endDate),
                'ageGroupAnalysis' => $this->reportModel->getAgeGroupAnalysis(),
                'growthStatsByGender' => $this->reportModel->getGrowthStatsByAgeAndGender()
            ];
            return $data;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getBMIDistribution() {
        try {
            return $this->reportModel->getBMIDistribution();
        } catch (Exception $e) {
            return ['error' => 'Failed to fetch BMI distribution data: ' . $e->getMessage()];
        }
    }

    public function getBMIDetails() {
        try {
            $startDate = $_POST['startDate'] ?? null;
            $endDate = $_POST['endDate'] ?? null;
            
            $data = $this->reportModel->getBMIDetails($startDate, $endDate);
            
            // Return success response
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
            exit;
        } catch (Exception $e) {
            error_log("Error in getBMIDetails: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => "Failed to fetch BMI details: " . $e->getMessage()
            ]);
            exit;
        }
    }

    public function getBMIDistributionByAgeAndSex() {
        try {
            $data = $this->reportModel->getBMIDistributionByAgeAndSex();
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function handleRequest() {
        try {
            $action = $_POST['action'] ?? '';
            
            switch($action) {
                case 'getBMIDetails':
                    $this->getBMIDetails();
                    break;
                
                case 'getBMIDistributionByAgeAndSex':
                    $response = $this->getBMIDistributionByAgeAndSex();
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    break;
                
                default:
                    throw new Exception("Invalid action");
            }
        } catch (Exception $e) {
            error_log("Error in ReportController: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}

if (isset($_POST['action'])) {
    $controller = new ReportController();
    $controller->handleRequest();
}
