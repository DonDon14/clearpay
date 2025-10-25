<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Dummy data for UI development - replace with actual controller data later
$students = [
    [
        'student_id' => 'ST001',
        'student_name' => 'John Doe',
        'total_payments' => 8,
        'total_paid' => 4500,
        'last_payment' => '2024-10-23 14:30:00',
        'contributions_count' => 3,
        'payment_status' => 'active',
        'email' => 'john.doe@email.com'
    ],
    [
        'student_id' => 'ST002',
        'student_name' => 'Jane Smith',
        'total_payments' => 12,
        'total_paid' => 6800,
        'last_payment' => '2024-10-25 09:15:00',
        'contributions_count' => 5,
        'payment_status' => 'active',
        'email' => 'jane.smith@email.com'
    ],
    [
        'student_id' => 'ST003',
        'student_name' => 'Mike Johnson',
        'total_payments' => 5,
        'total_paid' => 2250,
        'last_payment' => '2024-10-20 16:45:00',
        'contributions_count' => 2,
        'payment_status' => 'pending',
        'email' => 'mike.johnson@email.com'
    ],
    [
        'student_id' => 'ST004',
        'student_name' => 'Sarah Wilson',
        'total_payments' => 15,
        'total_paid' => 8950,
        'last_payment' => '2024-10-24 11:20:00',
        'contributions_count' => 6,
        'payment_status' => 'active',
        'email' => 'sarah.wilson@email.com'
    ],
    [
        'student_id' => 'ST005',
        'student_name' => 'David Brown',
        'total_payments' => 7,
        'total_paid' => 3200,
        'last_payment' => '2024-10-22 13:10:00',
        'contributions_count' => 4,
        'payment_status' => 'active',
        'email' => 'david.brown@email.com'
    ],
    [
        'student_id' => 'ST006',
        'student_name' => 'Lisa Garcia',
        'total_payments' => 3,
        'total_paid' => 1150,
        'last_payment' => '2024-10-15 08:30:00',
        'contributions_count' => 2,
        'payment_status' => 'inactive',
        'email' => 'lisa.garcia@email.com'
    ]
];

$payerStats = [
    'total_payers' => count($students),
    'active_payers' => count(array_filter($students, fn($s) => $s['payment_status'] === 'active')),
    'total_amount' => array_sum(array_column($students, 'total_paid')),
    'avg_payment_per_student' => array_sum(array_column($students, 'total_paid')) / count($students)
];
?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Payers Management</h1>
                    <p class="mb-0 text-muted">Manage and track student payments</p>
                </div>
                <div>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Payer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <?= $this->include('partials/card', [
                'title' => 'Total Payers',
                'text' => number_format($payerStats['total_payers']),
                'icon' => 'users',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= $this->include('partials/card', [
                'title' => 'Active Payers',
                'text' => number_format($payerStats['active_payers']),
                'icon' => 'user-check',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= $this->include('partials/card', [
                'title' => 'Total Amount',
                'text' => '₱' . number_format($payerStats['total_amount'], 2),
                'icon' => 'peso-sign',
                'iconColor' => 'text-warning'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= $this->include('partials/card', [
                'title' => 'Average per Student',
                'text' => '₱' . number_format($payerStats['avg_payment_per_student'], 2),
                'icon' => 'calculator',
                'iconColor' => 'text-info'
            ]) ?>
        </div>
    </div>

    <!-- Payers List -->
    <?= $this->include('partials/container-card', [
        'title' => 'Student Payers',
        'subtitle' => 'Complete list of all registered payers',
        'content' => '
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Total Payments</th>
                            <th>Total Amount</th>
                            <th>Last Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . implode('', array_map(function($student) {
                            $statusBadge = match($student['payment_status']) {
                                'active' => '<span class="badge bg-success">Active</span>',
                                'pending' => '<span class="badge bg-warning">Pending</span>',
                                'inactive' => '<span class="badge bg-secondary">Inactive</span>',
                                default => '<span class="badge bg-light text-dark">Unknown</span>'
                            };
                            
                            return '<tr>
                                <td><strong>' . htmlspecialchars($student['student_id']) . '</strong></td>
                                <td>' . htmlspecialchars($student['student_name']) . '</td>
                                <td>' . htmlspecialchars($student['email']) . '</td>
                                <td>' . number_format($student['total_payments']) . '</td>
                                <td>₱' . number_format($student['total_paid'], 2) . '</td>
                                <td>' . date('M j, Y', strtotime($student['last_payment'])) . '</td>
                                <td>' . $statusBadge . '</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-info" title="Export PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>';
                        }, $students))
                    . '</tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing 1 to ' . count($students) . ' of ' . count($students) . ' entries
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                    </ul>
                </nav>
            </div>
        '
    ]) ?>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus mb-2"></i><br>
                                Add New Payer
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-success w-100">
                                <i class="fas fa-file-export mb-2"></i><br>
                                Export All Data
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-info w-100">
                                <i class="fas fa-chart-bar mb-2"></i><br>
                                Generate Report
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-warning w-100">
                                <i class="fas fa-envelope mb-2"></i><br>
                                Send Reminders
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>