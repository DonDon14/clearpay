<!-- Quick Actions for Partial Payments -->
<div class="p-3">
    <div class="row g-3">
        <div class="col-lg-4 col-md-6">
            <div class="card bg-primary text-white shadow-sm rounded-3 hover-scale h-100" style="transition: transform 0.2s, box-shadow 0.2s; min-height: 120px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <div class="card-body d-flex align-items-center gap-3 h-100">
                    <div class="icon-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-plus fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-semibold">New Payment</h6>
                        <small class="text-white-75">Record fresh payment</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card bg-success text-white shadow-sm rounded-3 hover-scale h-100" style="transition: transform 0.2s, box-shadow 0.2s; min-height: 120px; cursor: pointer;" onclick="showPaymentStats()">
                <div class="card-body d-flex align-items-center gap-3 h-100">
                    <div class="icon-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-chart-line fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-semibold">View Stats</h6>
                        <small class="text-white-75">Payment analytics</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card bg-warning text-white shadow-sm rounded-3 hover-scale h-100" style="transition: transform 0.2s, box-shadow 0.2s; min-height: 120px; cursor: pointer;" onclick="sendReminders()">
                <div class="card-body d-flex align-items-center gap-3 h-100">
                    <div class="icon-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-bell fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-semibold">Send Reminders</h6>
                        <small class="text-white-75">Notify students</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    flex-shrink: 0;
    font-size: 1.25rem;
}

.hover-scale:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.text-white-75 {
    opacity: 0.75;
}
</style>