<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContributionModel;

class Analytics extends BaseController
{
    protected $contributionModel;
    protected $paymentModel;

    public function __construct()
    {
        $this->contributionModel = new ContributionModel();
        $this->paymentModel = new \App\Models\PaymentModel();
    }

    /**
     * Main analytics dashboard
     */
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Add profile picture for sidebar and header
        $session = session();
        $userId = $session->get('user-id');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        
        $profilePictureUrl = '';
        if (!empty($user['profile_picture'])) {
            $filename = basename($user['profile_picture']);
            $profilePictureUrl = base_url('test-profile-picture/' . $filename);
        }

        $data = [
            'pageTitle' => 'Analytics',
            'pageSubtitle' => 'Data insights and performance metrics',
            'title' => 'Analytics Dashboard',
            'overview' => $this->getOverviewStats(),
            'contributions' => $this->getContributionAnalytics(),
            'payments' => $this->getPaymentAnalytics(),
            'trends' => $this->getTrendAnalytics(),
            'charts' => $this->getChartData(),
            'profilePictureUrl' => $profilePictureUrl,
            'name' => $session->get('name'),
            'email' => $session->get('email')
        ];

        return view('admin/analytics', $data);
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats()
    {
        $db = \Config\Database::connect();
        
        // Total revenue (from payments) - include soft-deleted
        $totalRevenue = $db->table('payments')
                          ->selectSum('amount_paid')
                          ->whereIn('payment_status', ['fully paid', 'partial'])
                          ->where('deleted_at IS NULL')
                          ->get()
                          ->getRow();
        
        log_message('debug', 'Total Revenue Query Result: ' . json_encode($totalRevenue));
        
        $totalRevenue = $totalRevenue->amount_paid ?? 0;

        // Total contributions
        $totalContributions = $this->contributionModel->countAll();

        // Active contributors (unique payers who made payments)
        $activeContributors = $db->table('payments')
                                ->select('payer_id')
                                ->whereIn('payment_status', ['fully paid', 'partial'])
                                ->where('deleted_at IS NULL')
                                ->distinct()
                                ->countAllResults();

        // Detect database type for date functions and GROUP BY compatibility
        $dbDriver = $db->getPlatform();
        $isPostgres = (strpos(strtolower($dbDriver), 'postgre') !== false);
        
        // This month's revenue
        $thisMonthStart = date('Y-m-01 00:00:00');
        $thisMonthEnd = date('Y-m-t 23:59:59');
        
        $thisMonthRevenue = $db->table('payments')
                              ->selectSum('amount_paid')
                              ->whereIn('payment_status', ['fully paid', 'partial'])
                              ->where('deleted_at IS NULL')
                              ->where('created_at >=', $thisMonthStart)
                              ->where('created_at <=', $thisMonthEnd)
                              ->get()
                              ->getRow()
                              ->amount_paid ?? 0;

        // Last month's revenue for comparison
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));
        
        $lastMonthRevenue = $db->table('payments')
                              ->selectSum('amount_paid')
                              ->whereIn('payment_status', ['fully paid', 'partial'])
                              ->where('deleted_at IS NULL')
                              ->where('created_at >=', $lastMonthStart)
                              ->where('created_at <=', $lastMonthEnd)
                              ->get()
                              ->getRow()
                              ->amount_paid ?? 0;

        // Calculate month-over-month growth
        $monthlyGrowth = $lastMonthRevenue > 0 ? 
                        (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        // Total profit
        $profitData = $this->contributionModel->getProfitAnalytics();
        
        return [
            'total_revenue' => $totalRevenue,
            'total_contributions' => $totalContributions,
            'active_contributors' => $activeContributors,
            'monthly_revenue' => $thisMonthRevenue,
            'monthly_growth' => round($monthlyGrowth, 1),
            'total_profit' => $profitData['total_profit'] ?? 0,
            'avg_profit_margin' => round($profitData['avg_profit_margin'] ?? 0, 1)
        ];
    }

    /**
     * Get contribution analytics
     */
    private function getContributionAnalytics()
    {
        $profitAnalytics = $this->contributionModel->getProfitAnalytics();
        $topProfitable = $this->contributionModel->getTopProfitable(10);
        
        // Category breakdown
        $db = \Config\Database::connect();
        $categoryStats = $db->table('contributions')
                           ->select('category, COUNT(*) as count, SUM(amount) as total_amount, SUM((amount - cost_price)) as total_profit')
                           ->where('status', 'active')
                           ->groupBy('category')
                           ->orderBy('total_amount', 'DESC')
                           ->get()
                           ->getResultArray();

        return [
            'summary' => $profitAnalytics,
            'top_profitable' => $topProfitable,
            'by_category' => $categoryStats
        ];
    }

    /**
     * Get payment analytics
     */
    private function getPaymentAnalytics()
    {
        $db = \Config\Database::connect();
        
        // Detect database type for GROUP BY compatibility
        $dbDriver = $db->getPlatform();
        $isPostgres = (strpos(strtolower($dbDriver), 'postgre') !== false);
        
        // Payment status breakdown
        $statusStats = $db->table('payments')
                         ->select('payment_status as status, COUNT(*) as count, SUM(amount_paid) as total_amount')
                         ->where('deleted_at IS NULL')
                         ->groupBy('payment_status')
                         ->get()
                         ->getResultArray();

        // Payment method breakdown
        $methodStats = $db->table('payments')
                         ->select('payment_method, COUNT(*) as count, SUM(amount_paid) as total_amount')
                         ->whereIn('payment_status', ['fully paid', 'partial'])
                         ->where('deleted_at IS NULL')
                         ->groupBy('payment_method')
                         ->get()
                         ->getResultArray();

        // Recent payments
        $recentPayments = $db->table('payments p')
                            ->join('contributions c', 'p.contribution_id = c.id', 'left')
                            ->join('payers py', 'p.payer_id = py.id', 'left')
                            ->select('p.id, py.payer_name as student_name, c.title as contribution_title, p.amount_paid as amount, p.payment_method, p.payment_status as status, p.created_at')
                            ->where('p.deleted_at IS NULL')
                            ->orderBy('p.created_at', 'DESC')
                            ->limit(10)
                            ->get()
                            ->getResultArray();

        // Average transaction value
        $avgTransaction = $db->table('payments')
                            ->selectAvg('amount_paid')
                            ->whereIn('payment_status', ['fully paid', 'partial'])
                            ->where('deleted_at IS NULL')
                            ->get()
                            ->getRow()
                            ->amount_paid ?? 0;

        // Top payers (by total amount paid)
        // For PostgreSQL, all non-aggregated columns must be in GROUP BY
        $groupByColumns = $isPostgres 
            ? 'p.payer_id, py.payer_name, py.payer_id'
            : 'p.payer_id';
            
        $topPayers = $db->table('payments p')
                       ->join('payers py', 'p.payer_id = py.id', 'left')
                       ->select('py.payer_name, py.payer_id as payer_id_number, COUNT(p.id) as total_transactions, SUM(p.amount_paid) as total_paid')
                       ->whereIn('p.payment_status', ['fully paid', 'partial'])
                       ->where('p.deleted_at IS NULL')
                       ->groupBy($groupByColumns)
                       ->orderBy('total_paid', 'DESC')
                       ->limit(10)
                       ->get()
                       ->getResultArray();

        return [
            'by_status' => $statusStats,
            'by_method' => $methodStats,
            'recent_payments' => $recentPayments,
            'top_payers' => $topPayers,
            'avg_transaction' => round($avgTransaction, 2)
        ];
    }

    /**
     * Get trend analytics
     */
    private function getTrendAnalytics()
    {
        $db = \Config\Database::connect();
        
        // Detect database type for date functions (reuse from above)
        if (!isset($isPostgres)) {
            $dbDriver = $db->getPlatform();
            $isPostgres = (strpos(strtolower($dbDriver), 'postgre') !== false);
        }
        
        // Daily revenue for last 30 days
        $dateFunction = $isPostgres ? "DATE(created_at)" : "DATE(created_at)";
        $dailyRevenue = $db->table('payments')
                          ->select("{$dateFunction} as date, SUM(amount_paid) as total")
                          ->whereIn('payment_status', ['fully paid', 'partial'])
                          ->where('deleted_at IS NULL')
                          ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                          ->groupBy($dateFunction)
                          ->orderBy('date', 'ASC')
                          ->get()
                          ->getResultArray();

        // Monthly revenue for last 12 months
        $yearMonthSelect = $isPostgres 
            ? "EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month"
            : "YEAR(created_at) as year, MONTH(created_at) as month";
        $yearMonthGroupBy = $isPostgres
            ? "EXTRACT(YEAR FROM created_at), EXTRACT(MONTH FROM created_at)"
            : "YEAR(created_at), MONTH(created_at)";
            
        $monthlyRevenue = $db->table('payments')
                            ->select("{$yearMonthSelect}, SUM(amount_paid) as total")
                            ->whereIn('payment_status', ['fully paid', 'partial'])
                            ->where('deleted_at IS NULL')
                            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-12 months')))
                            ->groupBy($yearMonthGroupBy)
                            ->orderBy('year, month', 'ASC')
                            ->get()
                            ->getResultArray();

        // Transaction count trends
        $dailyTransactions = $db->table('payments')
                               ->select("{$dateFunction} as date, COUNT(*) as count")
                               ->whereIn('payment_status', ['fully paid', 'partial'])
                               ->where('deleted_at IS NULL')
                               ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                               ->groupBy($dateFunction)
                               ->orderBy('date', 'ASC')
                               ->get()
                               ->getResultArray();

        return [
            'daily_revenue' => $dailyRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'daily_transactions' => $dailyTransactions
        ];
    }

    /**
     * Get chart data for frontend
     */
    private function getChartData()
    {
        $trends = $this->getTrendAnalytics();
        
        // Format data for charts
        $revenueChart = [
            'labels' => array_column($trends['daily_revenue'], 'date'),
            'data' => array_column($trends['daily_revenue'], 'total')
        ];

        $transactionChart = [
            'labels' => array_column($trends['daily_transactions'], 'date'),
            'data' => array_column($trends['daily_transactions'], 'count')
        ];

        $monthlyChart = [
            'labels' => array_map(function($item) {
                return date('M Y', mktime(0, 0, 0, $item['month'], 1, $item['year']));
            }, $trends['monthly_revenue']),
            'data' => array_column($trends['monthly_revenue'], 'total')
        ];

        return [
            'daily_revenue' => $revenueChart,
            'daily_transactions' => $transactionChart,
            'monthly_revenue' => $monthlyChart
        ];
    }

    /**
     * Export analytics data
     */
    public function export($type = 'pdf')
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $data = [
            'overview' => $this->getOverviewStats(),
            'contributions' => $this->getContributionAnalytics(),
            'payments' => $this->getPaymentAnalytics(),
            'trends' => $this->getTrendAnalytics(),
            'generated_at' => date('Y-m-d H:i:s')
        ];

        switch ($type) {
            case 'pdf':
                return $this->exportPDF($data);
            case 'csv':
                return $this->exportCSV($data);
            case 'excel':
                return $this->exportExcel($data);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    /**
     * Export to PDF (HTML format for browser print/PDF)
     */
    private function exportPDF($data)
    {
        // Generate HTML report that can be printed to PDF from browser
        $html = $this->generatePDFHTML($data);
        
        // Return HTML that browsers can print to PDF
        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setHeader('Content-Disposition', 'inline; filename="ClearPay_Analytics_Report_' . date('Y-m-d_H-i-s') . '.html"')
            ->setBody($html);
    }

    /**
     * Export to CSV
     */
    private function exportCSV($data)
    {
        $filename = 'ClearPay_Analytics_Report_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for CSV download
        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        // Start output buffering
        ob_start();
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Report Header
        fputcsv($output, ['ClearPay Analytics Report']);
        fputcsv($output, ['Generated:', $data['generated_at']]);
        fputcsv($output, []);
        
        // Overview Statistics
        fputcsv($output, ['OVERVIEW STATISTICS']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Revenue', '₱' . number_format($data['overview']['total_revenue'], 2)]);
        fputcsv($output, ['Total Profit', '₱' . number_format($data['overview']['total_profit'], 2)]);
        fputcsv($output, ['Average Profit Margin', number_format($data['overview']['avg_profit_margin'], 1) . '%']);
        fputcsv($output, ['Active Contributors', $data['overview']['active_contributors']]);
        fputcsv($output, ['Total Contributions', $data['overview']['total_contributions']]);
        fputcsv($output, ['Monthly Revenue', '₱' . number_format($data['overview']['monthly_revenue'], 2)]);
        fputcsv($output, ['Monthly Growth', $data['overview']['monthly_growth'] . '%']);
        fputcsv($output, []);
        
        // Top Payers
        if (!empty($data['payments']['top_payers'])) {
            fputcsv($output, ['TOP PAYERS (Top 10)']);
            fputcsv($output, ['Rank', 'Name', 'ID', 'Total Paid', 'Transactions']);
            foreach ($data['payments']['top_payers'] as $index => $payer) {
                fputcsv($output, [
                    $index + 1,
                    $payer['payer_name'],
                    $payer['payer_id_number'],
                    '₱' . number_format($payer['total_paid'], 2),
                    $payer['total_transactions']
                ]);
            }
            fputcsv($output, []);
        }
        
        // Top Contributions
        if (!empty($data['contributions']['top_profitable'])) {
            fputcsv($output, ['TOP CONTRIBUTIONS (By Profit)']);
            fputcsv($output, ['Rank', 'Title', 'Category', 'Profit', 'Margin']);
            foreach ($data['contributions']['top_profitable'] as $index => $contribution) {
                fputcsv($output, [
                    $index + 1,
                    $contribution['title'],
                    $contribution['category'] ?? 'General',
                    '₱' . number_format($contribution['profit_amount'], 2),
                    number_format($contribution['profit_margin'], 1) . '%'
                ]);
            }
            fputcsv($output, []);
        }
        
        // Payment Methods
        if (!empty($data['payments']['by_method'])) {
            fputcsv($output, ['PAYMENT METHODS']);
            fputcsv($output, ['Method', 'Count', 'Total Amount']);
            foreach ($data['payments']['by_method'] as $method) {
                fputcsv($output, [
                    ucfirst($method['payment_method']),
                    $method['count'],
                    '₱' . number_format($method['total_amount'], 2)
                ]);
            }
            fputcsv($output, []);
        }
        
        // Payment Status
        if (!empty($data['payments']['by_status'])) {
            fputcsv($output, ['PAYMENT STATUS']);
            fputcsv($output, ['Status', 'Count', 'Total Amount']);
            foreach ($data['payments']['by_status'] as $status) {
                fputcsv($output, [
                    ucwords(str_replace('_', ' ', $status['status'])),
                    $status['count'],
                    '₱' . number_format($status['total_amount'], 2)
                ]);
            }
        }
        
        fclose($output);
        $csvContent = ob_get_clean();
        
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvContent);
    }

    /**
     * Export to Excel (tab-separated CSV with .xls extension)
     */
    private function exportExcel($data)
    {
        // Use CSV format with .xls extension (Excel will open it without warning if properly formatted)
        $filename = 'ClearPay_Analytics_Report_' . date('Y-m-d_H-i-s') . '.xls';
        
        // Use text/csv content type - Excel will handle it better
        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        ob_start();
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Report Header
        fputcsv($output, ['ClearPay Analytics Report'], "\t");
        fputcsv($output, ['Generated:', $data['generated_at']], "\t");
        fputcsv($output, [], "\t");
        
        // Overview Statistics
        fputcsv($output, ['OVERVIEW STATISTICS'], "\t");
        fputcsv($output, ['Metric', 'Value'], "\t");
        fputcsv($output, ['Total Revenue', '₱' . number_format($data['overview']['total_revenue'], 2)], "\t");
        fputcsv($output, ['Total Profit', '₱' . number_format($data['overview']['total_profit'], 2)], "\t");
        fputcsv($output, ['Average Profit Margin', number_format($data['overview']['avg_profit_margin'], 1) . '%'], "\t");
        fputcsv($output, ['Active Contributors', $data['overview']['active_contributors']], "\t");
        fputcsv($output, ['Total Contributions', $data['overview']['total_contributions']], "\t");
        fputcsv($output, ['Monthly Revenue', '₱' . number_format($data['overview']['monthly_revenue'], 2)], "\t");
        fputcsv($output, ['Monthly Growth', $data['overview']['monthly_growth'] . '%'], "\t");
        fputcsv($output, [], "\t");
        
        // Top Payers
        if (!empty($data['payments']['top_payers'])) {
            fputcsv($output, ['TOP PAYERS (Top 10)'], "\t");
            fputcsv($output, ['Rank', 'Name', 'ID', 'Total Paid', 'Transactions'], "\t");
            foreach ($data['payments']['top_payers'] as $index => $payer) {
                fputcsv($output, [
                    $index + 1,
                    $payer['payer_name'],
                    $payer['payer_id_number'],
                    '₱' . number_format($payer['total_paid'], 2),
                    $payer['total_transactions']
                ], "\t");
            }
            fputcsv($output, [], "\t");
        }
        
        // Top Contributions
        if (!empty($data['contributions']['top_profitable'])) {
            fputcsv($output, ['TOP CONTRIBUTIONS (By Profit)'], "\t");
            fputcsv($output, ['Rank', 'Title', 'Category', 'Profit', 'Margin'], "\t");
            foreach ($data['contributions']['top_profitable'] as $index => $contribution) {
                fputcsv($output, [
                    $index + 1,
                    $contribution['title'],
                    $contribution['category'] ?? 'General',
                    '₱' . number_format($contribution['profit_amount'], 2),
                    number_format($contribution['profit_margin'], 1) . '%'
                ], "\t");
            }
            fputcsv($output, [], "\t");
        }
        
        // Payment Methods
        if (!empty($data['payments']['by_method'])) {
            fputcsv($output, ['PAYMENT METHODS'], "\t");
            fputcsv($output, ['Method', 'Count', 'Total Amount'], "\t");
            foreach ($data['payments']['by_method'] as $method) {
                fputcsv($output, [
                    ucfirst($method['payment_method']),
                    $method['count'],
                    '₱' . number_format($method['total_amount'], 2)
                ], "\t");
            }
            fputcsv($output, [], "\t");
        }
        
        // Payment Status
        if (!empty($data['payments']['by_status'])) {
            fputcsv($output, ['PAYMENT STATUS'], "\t");
            fputcsv($output, ['Status', 'Count', 'Total Amount'], "\t");
            foreach ($data['payments']['by_status'] as $status) {
                fputcsv($output, [
                    ucwords(str_replace('_', ' ', $status['status'])),
                    $status['count'],
                    '₱' . number_format($status['total_amount'], 2)
                ], "\t");
            }
        }
        
        fclose($output);
        $content = ob_get_clean();
        
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }
    
    /**
     * Generate HTML for PDF
     */
    private function generatePDFHTML($data)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>ClearPay Analytics Report</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Arial', sans-serif;
                    padding: 20px;
                    color: #333;
                    line-height: 1.6;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 3px solid #3b82f6;
                }
                .header h1 {
                    color: #3b82f6;
                    font-size: 28px;
                    margin-bottom: 5px;
                }
                .header p {
                    color: #666;
                    font-size: 14px;
                }
                .section {
                    margin-bottom: 30px;
                }
                .section-title {
                    background-color: #f8f9fa;
                    padding: 10px 15px;
                    border-left: 4px solid #3b82f6;
                    margin-bottom: 15px;
                    font-size: 18px;
                    font-weight: bold;
                    color: #333;
                }
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 15px;
                    margin-bottom: 20px;
                }
                .stat-card {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    border-left: 4px solid #3b82f6;
                }
                .stat-label {
                    font-size: 12px;
                    color: #666;
                    text-transform: uppercase;
                    margin-bottom: 5px;
                }
                .stat-value {
                    font-size: 24px;
                    font-weight: bold;
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table th {
                    background-color: #3b82f6;
                    color: white;
                    padding: 12px;
                    text-align: left;
                    font-weight: bold;
                }
                table td {
                    padding: 10px 12px;
                    border-bottom: 1px solid #ddd;
                }
                table tr:nth-child(even) {
                    background-color: #f8f9fa;
                }
                .text-right {
                    text-align: right;
                }
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 2px solid #ddd;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
                @media print {
                    body { padding: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>ClearPay Analytics Report</h1>
                <p>Generated on <?= date('F j, Y \a\t g:i A', strtotime($data['generated_at'])) ?></p>
            </div>

            <!-- Overview Statistics -->
            <div class="section">
                <div class="section-title">Overview Statistics</div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">₱<?= number_format($data['overview']['total_revenue'], 2) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Profit</div>
                        <div class="stat-value">₱<?= number_format($data['overview']['total_profit'], 2) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Active Contributors</div>
                        <div class="stat-value"><?= number_format($data['overview']['active_contributors']) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Contributions</div>
                        <div class="stat-value"><?= number_format($data['overview']['total_contributions']) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Average Profit Margin</div>
                        <div class="stat-value"><?= number_format($data['overview']['avg_profit_margin'], 1) ?>%</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Monthly Growth</div>
                        <div class="stat-value"><?= $data['overview']['monthly_growth'] ?>%</div>
                    </div>
                </div>
            </div>

            <!-- Top Payers -->
            <?php if (!empty($data['payments']['top_payers'])): ?>
            <div class="section">
                <div class="section-title">Top Payers (Top 10)</div>
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>ID</th>
                            <th class="text-right">Total Paid</th>
                            <th class="text-right">Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['payments']['top_payers'] as $index => $payer): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($payer['payer_name']) ?></td>
                            <td><?= htmlspecialchars($payer['payer_id_number']) ?></td>
                            <td class="text-right">₱<?= number_format($payer['total_paid'], 2) ?></td>
                            <td class="text-right"><?= $payer['total_transactions'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Top Contributions -->
            <?php if (!empty($data['contributions']['top_profitable'])): ?>
            <div class="section">
                <div class="section-title">Top Performing Contributions</div>
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th class="text-right">Profit</th>
                            <th class="text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['contributions']['top_profitable'] as $index => $contribution): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($contribution['title']) ?></td>
                            <td><?= htmlspecialchars($contribution['category'] ?? 'General') ?></td>
                            <td class="text-right">₱<?= number_format($contribution['profit_amount'] ?? 0, 2) ?></td>
                            <td class="text-right"><?= number_format($contribution['profit_margin'] ?? 0, 1) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="footer">
                <p>This report was generated by ClearPay Analytics System</p>
                <p>For questions or concerns, please contact the system administrator</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}