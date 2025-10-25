<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PaymentModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $paymentModel = new PaymentModel();
        $recentPayments = $paymentModel->getRecentPayments(3); // Last 3 payments

        return view('admin/dashboard', [
            'recentPayments' => $recentPayments,
            'pageTitle' => 'Dashboard'
        ]);
    }

    public function recentPayments()
{
    $payerModel = new PayerModel();
    $recentPayments = $payerModel->getRecentPayments(3);
    return view('partials/recent_payments_list', ['recentPayments' => $recentPayments]);
}
}
