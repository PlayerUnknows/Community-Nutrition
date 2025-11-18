<!-- Overall Report Content -->
<link rel="stylesheet" href="../../assets/css/overallreport.css">
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
                       
                            </div>
                        </div>
                    </div>
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