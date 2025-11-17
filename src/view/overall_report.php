<!-- Overall Report Content -->
<style>
    /* Override monitoring.php styles for the Overall Report */
    #overall-report .card-body {
        padding: 1rem !important;
        overflow: visible !important;
    }
    
    #overall-report .table {
        width: 100% !important;
        margin-bottom: 1rem !important;
        table-layout: auto !important;
    }
    
    #overall-report .table th,
    #overall-report .table td {
        padding: 0.5rem !important;
        white-space: normal !important;
    }
    
    #overall-report .report-content {
        padding: 1rem !important;
    }
    
    /* Specific styles for the OPT Plus report */
    #overall-report .table-sm th,
    #overall-report .table-sm td {
        padding: 0.3rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Print styles */
    @media print {
        body * {
            visibility: hidden;
        }
        #overall-report, #overall-report * {
            visibility: visible;
        }
        #overall-report {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- DataTables JS (after jQuery) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<div id="overall-report">
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">OPT PLUS Nutrition Monitoring Report</h5>
                    <div>
                        <button class="btn btn-sm btn-primary" id="exportOverallReportBtn">
                            <i class="fas fa-file-export me-1"></i> Export Report
                        </button>
                        <button class="btn btn-sm btn-secondary" id="printOverallReportBtn">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Location Information -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">PROVINCE:</label>
                                <input type="text" class="form-control" id="provinceInput" value="Rizal Province">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">BARANGAY:</label>
                                <input type="text" class="form-control" id="barangayInput" value="San Juan">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">MUNICIPALITY:</label>
                                <input type="text" class="form-control" id="municipalityInput" value="CAINTA">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">PSGC:</label>
                                <input type="text" class="form-control" id="psgcInput" value="0405082016">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Regn:</label>
                                <input type="text" class="form-control" id="regionInput" value="IVA CALABARZON">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Total Popn Barangay:</label>
                                <input type="text" class="form-control" id="totalPopnInput" value="108,222">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Estimated Popn of Children 0-59 mos in Barangay:</label>
                                <input type="text" class="form-control" id="estimatedChildrenInput" value="9,659">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">OPT Plus Coverage:</label>
                                <input type="text" class="form-control" id="optCoverageInput" value="61.2%">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Total Indigenous Preschool Children Measured:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="totalIndigenousInput" value="0">
                                    <span class="input-group-text">of</span>
                                    <input type="text" class="form-control" id="totalIndigenousInput2" value="0">
                                </div>
                            </div>
                            <div class="form-group mb-3 text-center">
                                <img src="../../assets/img/opt-plus-logo.png" alt="OPT PLUS 2023" class="img-fluid" style="max-height: 100px;">
                                <h5 class="mt-2">OPT PLUS 2023</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Controls - Compact Version -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex align-items-end gap-2 p-2 bg-light rounded">
                                <div style="flex: 0 0 300px;">
                                    <label for="overallDateRangeFilter" class="form-label mb-1 small fw-bold">Date Range</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="text" class="form-control" id="overallDateRangeFilter" placeholder="Select date range">
                                    </div>
                                </div>
                                <!-- <div>
                                    <button class="btn btn-primary btn-sm" id="generateReportBtn">
                                        <i class="fas fa-sync-alt me-1"></i> Generate
                                    </button>
                                </div> -->
                            </div>
                        </div>
                    </div>

                    <!-- Summary Report Cards - Compact -->
                    <!-- <div class="row g-2 mb-3" id="summaryCards">
                        <div class="col-md-3">
                            <div class="card border-primary h-50">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0" style="font-size: 0.75rem; color: #6c757d;">Total BMI</p>
                                            <h5 class="mb-0 fw-bold" id="summaryTotalBMI">0</h5>
                                        </div>
                                        <i class="fas fa-weight text-primary" style="font-size: 1.5rem; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success h-100">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0" style="font-size: 0.75rem; color: #6c757d;">Total Height</p>
                                            <h5 class="mb-0 fw-bold" id="summaryTotalHeight">0</h5>
                                        </div>
                                        <i class="fas fa-ruler-vertical text-success" style="font-size: 1.5rem; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning h-100">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0" style="font-size: 0.75rem; color: #6c757d;">Total Arm Circ.</p>
                                            <h5 class="mb-0 fw-bold" id="summaryTotalArm">0</h5>
                                        </div>
                                        <i class="fas fa-child text-warning" style="font-size: 1.5rem; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info h-100">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="mb-0" style="font-size: 0.75rem; color: #6c757d;">Grand Total</p>
                                            <h5 class="mb-0 fw-bold" id="summaryGrandTotal">0</h5>
                                        </div>
                                        <i class="fas fa-users text-info" style="font-size: 1.5rem; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    
                    <!-- OPT Plus Nutrition Status Report Table (dynamic, rendered by JS) -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">OPT Plus Nutrition Status Report</h5>
                        </div>
                        <div class="card-body report-content">
                            <div class="text-center my-5">
                                <div class="spinner-border" role="status"></div>
                                <p class="mt-2">Loading OPT Plus Nutrition Status Report...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Load the Overall Report Module -->
<script>
    // This will be a signal to initialize the report when this page is shown
    $(document).ready(function() {
        // The actual initialization happens in overall_report.js
        console.log("Overall report page loaded and ready");
    });
</script>