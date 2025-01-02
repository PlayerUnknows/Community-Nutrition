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

    public function generateReport() {
        try {
            $data = [
                'growthTrends' => $this->getGrowthTrendsData(),
                'nutritionalStatus' => $this->getNutritionalStatusSummary(),
                'ageGroupAnalysis' => $this->getAgeGroupAnalysis()
            ];
            return $data;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
