<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PaymentModel;
use App\Models\ContributionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $paymentModel = new PaymentModel();
        $allPayments = $paymentModel
            ->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, payers.contact_number, payers.email_address, contributions.title as contribution_title')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();

        // Get User Information
        $userModel = new UserModel();
        $user = $userModel->select('username')
                          ->where('id', session()->get('user-id'))
                          ->first();

        // --- Fetch Total Collections ---
        $totalModel = new PaymentModel();
        $totalCollectionsRow = $totalModel
            ->selectSum('amount_paid')
            ->where('payment_status', 'fully paid')
            ->first();

        $totalCollections = 0.0;
        if (!empty($totalCollectionsRow)) {
            $totalCollections = isset($totalCollectionsRow['amount_paid'])
                                ? (float)$totalCollectionsRow['amount_paid']
                                : (float)array_values($totalCollectionsRow)[0];
        }
        $totalCollections = number_format($totalCollections, 2);

        // --- Fetch Other Stats ---
        $paymentModel = new PaymentModel();
        $verifiedPayments = $paymentModel->where('payment_status', 'fully paid')->countAllResults();

        $paymentModel = new PaymentModel();
        $partialPayments  = $paymentModel->where('LOWER(payment_status)', 'partial')->countAllResults();

        $paymentModel = new PaymentModel();
        $todayPayments    = $paymentModel->where('DATE(payment_date)', date('Y-m-d'))->countAllResults();

        // --- Fetch Recent Payments ---
        $recentPayments = $paymentModel
            ->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, payers.contact_number, payers.email_address, contributions.title as contribution_title, contributions.id as contrib_id')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->orderBy('payments.payment_date', 'DESC')
            ->limit(4)
            ->findAll(); // last 4 payments

        // Add computed status to each payment
        foreach ($recentPayments as &$payment) {
            $payerId = $payment['payer_id'];
            $contributionId = $payment['contrib_id'] ?? $payment['contribution_id'] ?? null;
            $payment['computed_status'] = $paymentModel->getPaymentStatus($payerId, $contributionId);
        }

        // Add computed status to all payments as well
        foreach ($allPayments as &$payment) {
            $payerId = $payment['payer_id'];
            $contributionId = $payment['contribution_id'] ?? null;
            $payment['computed_status'] = $paymentModel->getPaymentStatus($payerId, $contributionId);
        }

        // --- Fetch Contributions for Modal ---
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // --- Prepare Data for View ---
        $data = [
            'totalCollections' => $totalCollections,
            'verifiedPayments'  => $verifiedPayments,
            'partialPayments'   => $partialPayments,
            'todayPayments'     => $todayPayments,
            'recentPayments'    => $recentPayments,
            'allPayments'       => $allPayments,
            'contributions'     => $contributions,
            'title'             => 'Admin Dashboard',
            'pageTitle'         => 'Dashboard',
            'pageSubtitle'      => 'Welcome back ' . ucwords($user['username'] ?? 'User') . '!',
            'username'          => $user['username'] ?? 'User', 
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Global search functionality
     * Searches across payments, contributions, and payers
     */
    public function search()
    {
        try {
            $query = $this->request->getGet('q') ?? '';
            
            // Log the search query
            log_message('debug', 'Search query: ' . $query);
            
            if (empty($query) || strlen($query) < 2) {
                return $this->response->setJSON([
                    'success' => true,
                    'results' => [],
                    'message' => 'Query too short'
                ]);
            }

            $results = [];
        
        // Search payments
        $paymentModel = new PaymentModel();
        $payments = $paymentModel
            ->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, contributions.title as contribution_title')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->groupStart()
                ->like('payers.payer_name', $query)
                ->orLike('payers.payer_id', $query)
                ->orLike('payments.receipt_number', $query)
                ->orLike('contributions.title', $query)
            ->groupEnd()
            ->limit(5)
            ->findAll();

        foreach ($payments as $payment) {
            $results[] = [
                'type' => 'payment',
                'id' => $payment['id'],
                'title' => $payment['payer_name'] . ' - ' . $payment['contribution_title'],
                'subtitle' => '₱' . number_format($payment['amount_paid'], 2) . ' • ' . date('M d, Y', strtotime($payment['payment_date'])),
                'icon' => 'fa-money-bill-wave',
                'url' => base_url('payments') . '#payment-' . $payment['id'],
                'data' => $payment
            ];
        }
        
        // Search contributions
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel
            ->like('title', $query)
            ->orLike('description', $query)
            ->limit(5)
            ->findAll();

        foreach ($contributions as $contribution) {
            $results[] = [
                'type' => 'contribution',
                'id' => $contribution['id'],
                'title' => $contribution['title'],
                'subtitle' => '₱' . number_format($contribution['amount'], 2) . ' • ' . ucfirst($contribution['status']),
                'icon' => 'fa-tag',
                'url' => base_url('contributions') . '#contribution-' . $contribution['id'],
                'data' => $contribution
            ];
        }
        
        // Search payers
        $payerModel = new \App\Models\PayerModel();
        $payers = $payerModel
            ->like('payer_name', $query)
            ->orLike('payer_id', $query)
            ->orLike('email_address', $query)
            ->limit(5)
            ->findAll();

        foreach ($payers as $payer) {
            $results[] = [
                'type' => 'payer',
                'id' => $payer['id'],
                'title' => $payer['payer_name'],
                'subtitle' => $payer['payer_id'] . ($payer['email_address'] ? ' • ' . $payer['email_address'] : ''),
                'icon' => 'fa-user',
                'url' => base_url('payers') . '#payer-' . $payer['id'],
                'data' => $payer
            ];
        }

            return $this->response->setJSON([
                'success' => true,
                'results' => array_slice($results, 0, 10) // Limit to 10 total results
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error performing search: ' . $e->getMessage(),
                'results' => []
            ]);
        }
    }


}
