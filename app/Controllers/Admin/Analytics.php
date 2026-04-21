<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContributionModel;
use App\Services\PythonAnalyticsService;

class Analytics extends BaseController
{
    protected $contributionModel;
    protected $paymentModel;
    protected PythonAnalyticsService $pythonAnalyticsService;

    public function __construct()
    {
        $this->contributionModel = new ContributionModel();
        $this->paymentModel = new \App\Models\PaymentModel();
        $this->pythonAnalyticsService = new PythonAnalyticsService();
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

        $analysis = $this->pythonAnalyticsService->generateAnalytics();

        $data = [
            'pageTitle' => 'Analytics',
            'pageSubtitle' => 'Data insights and performance metrics',
            'title' => 'Analytics Dashboard',
            'overview' => $analysis['overview'] ?? [],
            'contributions' => $analysis['contributions'] ?? [],
            'payments' => $analysis['payments'] ?? [],
            'trends' => $analysis['trends'] ?? [],
            'charts' => $analysis['charts'] ?? [],
            'generatedAt' => $analysis['generated_at'] ?? date('c'),
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
            ? 'p.payer_id, py.payer_name, py.payer_id, py.profile_picture, py.id'
            : 'p.payer_id';
            
        $topPayers = $db->table('payments p')
                       ->join('payers py', 'p.payer_id = py.id', 'left')
                       ->select('py.payer_name, py.payer_id as payer_id_number, py.profile_picture, py.id as payer_db_id, COUNT(p.id) as total_transactions, SUM(p.amount_paid) as total_paid')
                       ->whereIn('p.payment_status', ['fully paid', 'partial'])
                       ->where('p.deleted_at IS NULL')
                       ->groupBy($groupByColumns)
                       ->orderBy('total_paid', 'DESC')
                       ->limit(10)
                       ->get()
                       ->getResultArray();
        
        // Normalize profile pictures for top payers
        foreach ($topPayers as &$payer) {
            $payer['profile_picture'] = $this->normalizeProfilePicturePath(
                $payer['profile_picture'] ?? null,
                $payer['payer_db_id'] ?? null,
                null,
                'payer'
            );
        }

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

        if (! in_array($type, ['pdf', 'csv', 'excel'], true)) {
            return redirect()->back()->with('error', 'Invalid export format');
        }

        if ($type === 'pdf') {
            $analysis = $this->pythonAnalyticsService->generateAnalytics();
            return $this->exportPDF($analysis);
        }

        $report = $this->pythonAnalyticsService->generateReport($type);
        return $this->response->download($report['path'], null)->setFileName($report['filename']);
    }

    /**
     * Export to PDF (HTML format for browser print/PDF)
     */
    private function exportPDF($data)
    {
        $html = $this->generatePDFHTML($data);

        if (class_exists('\TCPDF')) {
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('ClearPay');
            $pdf->SetAuthor('ClearPay');
            $pdf->SetTitle('ClearPay Analytics Report');
            $pdf->SetSubject('Analytics Export');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 12);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');

            $filename = 'ClearPay_Analytics_Report_' . date('Y-m-d_H-i-s') . '.pdf';
            $binary = $pdf->Output($filename, 'S');

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($binary);
        }

        // Fallback if TCPDF is unavailable in runtime.
        $report = $this->pythonAnalyticsService->generateReport('pdf');
        return $this->response->download($report['path'], null)->setFileName($report['filename']);
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
        <style>
            body { font-family: helvetica, sans-serif; font-size: 10px; color: #1f2937; }
            .title { font-size: 20px; font-weight: bold; color: #1d4ed8; }
            .sub { color: #4b5563; font-size: 10px; margin-bottom: 10px; }
            .section-title { font-size: 12px; font-weight: bold; background-color: #e5e7eb; padding: 6px; margin-top: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 8px; }
            th { background-color: #2563eb; color: #ffffff; font-weight: bold; font-size: 9px; padding: 5px; border: 1px solid #d1d5db; }
            td { font-size: 9px; padding: 5px; border: 1px solid #d1d5db; }
            .right { text-align: right; }
            .muted { color: #6b7280; font-size: 8px; }
        </style>

        <div class="title">ClearPay Analytics Report</div>
        <div class="sub">Generated on <?= esc(date('F j, Y \a\t g:i A', strtotime($data['generated_at'] ?? date('c')))) ?></div>

        <div class="section-title">Overview Statistics</div>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="right">Value</th>
                    <th>Metric</th>
                    <th class="right">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Revenue</td>
                    <td class="right">&#8369;<?= number_format((float)($data['overview']['total_revenue'] ?? 0), 2) ?></td>
                    <td>Total Profit</td>
                    <td class="right">&#8369;<?= number_format((float)($data['overview']['total_profit'] ?? 0), 2) ?></td>
                </tr>
                <tr>
                    <td>Monthly Revenue</td>
                    <td class="right">&#8369;<?= number_format((float)($data['overview']['monthly_revenue'] ?? 0), 2) ?></td>
                    <td>Monthly Growth</td>
                    <td class="right"><?= number_format((float)($data['overview']['monthly_growth'] ?? 0), 1) ?>%</td>
                </tr>
                <tr>
                    <td>Active Contributors</td>
                    <td class="right"><?= number_format((int)($data['overview']['active_contributors'] ?? 0)) ?></td>
                    <td>Total Contributions</td>
                    <td class="right"><?= number_format((int)($data['overview']['total_contributions'] ?? 0)) ?></td>
                </tr>
                <tr>
                    <td>Average Profit Margin</td>
                    <td class="right"><?= number_format((float)($data['overview']['avg_profit_margin'] ?? 0), 1) ?>%</td>
                    <td>Outstanding Balance</td>
                    <td class="right">&#8369;<?= number_format((float)($data['overview']['total_outstanding_balance'] ?? 0), 2) ?></td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($data['payments']['top_payers'])): ?>
            <div class="section-title">Top Payers</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:8%;">#</th>
                        <th style="width:36%;">Name</th>
                        <th style="width:18%;">ID</th>
                        <th style="width:20%;" class="right">Total Paid</th>
                        <th style="width:18%;" class="right">Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($data['payments']['top_payers'], 0, 10) as $index => $payer): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc((string)($payer['payer_name'] ?? 'Unknown')) ?></td>
                            <td><?= esc((string)($payer['payer_id_number'] ?? '-')) ?></td>
                            <td class="right">&#8369;<?= number_format((float)($payer['total_paid'] ?? 0), 2) ?></td>
                            <td class="right"><?= number_format((int)($payer['total_transactions'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!empty($data['contributions']['top_profitable'])): ?>
            <div class="section-title">Top Performing Contributions</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:8%;">#</th>
                        <th style="width:44%;">Title</th>
                        <th style="width:20%;">Category</th>
                        <th style="width:14%;" class="right">Profit</th>
                        <th style="width:14%;" class="right">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($data['contributions']['top_profitable'], 0, 10) as $index => $contribution): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc((string)($contribution['title'] ?? 'Untitled')) ?></td>
                            <td><?= esc((string)($contribution['category'] ?? 'General')) ?></td>
                            <td class="right">&#8369;<?= number_format((float)($contribution['profit_amount'] ?? 0), 2) ?></td>
                            <td class="right"><?= number_format((float)($contribution['profit_margin'] ?? 0), 1) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="muted">Generated by ClearPay Analytics</div>
        <?php
        return ob_get_clean();
    }
}
