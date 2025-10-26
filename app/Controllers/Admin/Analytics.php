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

        // This month's revenue
        $thisMonthRevenue = $db->table('payments')
                              ->selectSum('amount_paid')
                              ->whereIn('payment_status', ['fully paid', 'partial'])
                              ->where('deleted_at IS NULL')
                              ->where('MONTH(created_at)', date('m'))
                              ->where('YEAR(created_at)', date('Y'))
                              ->get()
                              ->getRow()
                              ->amount_paid ?? 0;

        // Last month's revenue for comparison
        $lastMonthRevenue = $db->table('payments')
                              ->selectSum('amount_paid')
                              ->whereIn('payment_status', ['fully paid', 'partial'])
                              ->where('deleted_at IS NULL')
                              ->where('MONTH(created_at)', date('m', strtotime('-1 month')))
                              ->where('YEAR(created_at)', date('Y', strtotime('-1 month')))
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

        return [
            'by_status' => $statusStats,
            'by_method' => $methodStats,
            'recent_payments' => $recentPayments,
            'avg_transaction' => round($avgTransaction, 2)
        ];
    }

    /**
     * Get trend analytics
     */
    private function getTrendAnalytics()
    {
        $db = \Config\Database::connect();
        
        // Daily revenue for last 30 days
        $dailyRevenue = $db->table('payments')
                          ->select('DATE(created_at) as date, SUM(amount_paid) as total')
                          ->whereIn('payment_status', ['fully paid', 'partial'])
                          ->where('deleted_at IS NULL')
                          ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                          ->groupBy('DATE(created_at)')
                          ->orderBy('date', 'ASC')
                          ->get()
                          ->getResultArray();

        // Monthly revenue for last 12 months
        $monthlyRevenue = $db->table('payments')
                            ->select('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount_paid) as total')
                            ->whereIn('payment_status', ['fully paid', 'partial'])
                            ->where('deleted_at IS NULL')
                            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-12 months')))
                            ->groupBy('YEAR(created_at), MONTH(created_at)')
                            ->orderBy('year, month', 'ASC')
                            ->get()
                            ->getResultArray();

        // Transaction count trends
        $dailyTransactions = $db->table('payments')
                               ->select('DATE(created_at) as date, COUNT(*) as count')
                               ->whereIn('payment_status', ['fully paid', 'partial'])
                               ->where('deleted_at IS NULL')
                               ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                               ->groupBy('DATE(created_at)')
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
     * Export to PDF
     */
    private function exportPDF($data)
    {
        // Simple HTML to PDF export
        $filename = 'ClearPay_Analytics_Report_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // For now, return a simple response indicating PDF export
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody('PDF Export functionality - To be implemented with TCPDF or similar library');
    }

    /**
     * Export to CSV
     */
    private function exportCSV($data)
    {
        $filename = 'analytics_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for CSV download
        $this->response->setHeader('Content-Type', 'text/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        // Start output buffering
        ob_start();
        $output = fopen('php://output', 'w');
        
        // Overview Statistics
        fputcsv($output, ['OVERVIEW STATISTICS']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Revenue', '₱' . number_format($data['overview']['total_revenue'], 2)]);
        fputcsv($output, ['Total Profit', '₱' . number_format($data['overview']['total_profit'], 2)]);
        fputcsv($output, ['Active Contributors', $data['overview']['active_contributors']]);
        fputcsv($output, ['Monthly Revenue', '₱' . number_format($data['overview']['monthly_revenue'], 2)]);
        fputcsv($output, []);
        
        // Recent Payments
        if (!empty($data['payments']['recent_payments'])) {
            fputcsv($output, ['RECENT PAYMENTS']);
            fputcsv($output, ['Date', 'Student Name', 'Contribution', 'Amount', 'Payment Method']);
            foreach ($data['payments']['recent_payments'] as $payment) {
                fputcsv($output, [
                    date('Y-m-d H:i:s', strtotime($payment['created_at'])),
                    $payment['student_name'],
                    $payment['contribution_title'],
                    '₱' . number_format($payment['amount'], 2),
                    ucfirst($payment['payment_method'])
                ]);
            }
        }
        
        fputcsv($output, []);
        fputcsv($output, ['Report Generated:', $data['generated_at']]);
        
        fclose($output);
        $csvContent = ob_get_clean();
        
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvContent);
    }

    /**
     * Export to Excel
     */
    private function exportExcel($data)
    {
        // For now, create an Excel-compatible CSV
        $filename = 'analytics_report_' . date('Y-m-d_H-i-s') . '.xls';
        
        $this->response->setHeader('Content-Type', 'application/vnd.ms-excel');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        ob_start();
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['ClearPay Analytics Report']);
        fputcsv($output, ['Generated:', $data['generated_at']]);
        fputcsv($output, []);
        
        // Add summary data
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Total Revenue', '₱' . number_format($data['overview']['total_revenue'], 2)]);
        fputcsv($output, ['Total Profit', '₱' . number_format($data['overview']['total_profit'], 2)]);
        fputcsv($output, ['Active Contributors', $data['overview']['active_contributors']]);
        
        fclose($output);
        $content = ob_get_clean();
        
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }
}