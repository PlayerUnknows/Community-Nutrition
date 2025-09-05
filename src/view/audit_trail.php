<?php require_once '../includes/header.php'; ?>

<div class="tab-pane fade" id="audit" role="tabpanel" aria-labelledby="audit-tab">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Audit Trail</h5>

            <!-- Search and length controls -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="search" class="form-control" id="auditSearch" placeholder="Search audit trail...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="auditsPerPage">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>

            <!-- Audit table -->
            <div class="table-responsive">
                <table id="auditTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../script/audit_trail.js"></script>
