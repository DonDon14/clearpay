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
            ->select('payers.*, contributions.title as contribution_title')
            ->join('contributions', 'payers.contribution_id = contributions.id', 'left')
            ->orderBy('payment_date', 'DESC')
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
            ->select('payers.*, contributions.title as contribution_title')
            ->join('contributions', 'payers.contribution_id = contributions.id', 'left')
            ->orderBy('payment_date', 'DESC')
            ->findAll(4); // last 4 payments

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

    public function recentPayments($limit = 4)
    {
        $paymentModel = new PaymentModel();
        $recentPayments = $paymentModel
            ->select('payers.*, contributions.title as contribution_title')
            ->join('contributions', 'payers.contribution_id = contributions.id', 'left')
            ->orderBy('payment_date', 'DESC')
            ->findAll($limit);

        return view('admin/dashboard', ['recentPayments' => $recentPayments]);
    }


}
