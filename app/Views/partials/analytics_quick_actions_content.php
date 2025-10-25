<div class="row g-3">
    <div class="col-md-4">
        <div class="card bg-primary text-white h-100 quick-action-card" onclick="refreshAnalytics()" style="cursor: pointer;">
            <div class="card-body d-flex align-items-center gap-3 h-100">
                <div class="icon-circle">
                    <i class="fas fa-sync-alt fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold">Refresh Data</h6>
                    <small class="text-white-75">Update analytics</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white h-100 quick-action-card" onclick="exportAnalytics()" style="cursor: pointer;">
            <div class="card-body d-flex align-items-center gap-3 h-100">
                <div class="icon-circle">
                    <i class="fas fa-download fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold">Export Report</h6>
                    <small class="text-white-75">Download PDF/CSV</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white h-100 quick-action-card" onclick="viewDetailedReports()" style="cursor: pointer;">
            <div class="card-body d-flex align-items-center gap-3 h-100">
                <div class="icon-circle">
                    <i class="fas fa-chart-line fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold">Detailed Reports</h6>
                    <small class="text-white-75">Advanced insights</small>
                </div>
            </div>
        </div>
    </div>
</div>