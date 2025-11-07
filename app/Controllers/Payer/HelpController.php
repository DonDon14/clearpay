<?php

namespace App\Controllers\Payer;

use App\Controllers\BaseController;

class HelpController extends BaseController
{
    public function index()
    {
        // Check if payer is logged in
        if (!session()->get('payer_id')) {
            return redirect()->to('/payer/login');
        }

        $data = [
            'title' => 'Help & Support',
            'pageTitle' => 'Help & Support',
            'pageSubtitle' => 'Get assistance and find answers to common questions',
            'payerData' => [
                'profile_picture' => session('payer_profile_picture'),
                'student_id' => session('payer_student_id')
            ]
        ];

        return view('payer/help', $data);
    }
}

