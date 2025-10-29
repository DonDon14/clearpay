<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ContributionModel;
use App\Models\PaymentModel;

class SidebarController extends BaseController
{

    public function payments()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Fetch contributions for the modal dropdown
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // Fetch ALL payments (not just recent 10) like dashboard
        $paymentModel = new PaymentModel();
        $recentPayments = $paymentModel->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, payers.contact_number, payers.email_address, contributions.title as contribution_title, contributions.id as contrib_id')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();

        // Add computed status to each payment
        foreach ($recentPayments as &$payment) {
            $payerId = $payment['payer_id'];
            $contributionId = $payment['contrib_id'] ?? $payment['contribution_id'] ?? null;
            $payment['computed_status'] = $paymentModel->getPaymentStatus($payerId, $contributionId);
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Admin Payments',
            'pageTitle' => 'Payments',
            'pageSubtitle' => 'Manage payments and transactions',
            'username' => session()->get('username'),
            'contributions' => $contributions,
            'recentPayments' => $recentPayments
        ];

        return view('admin/payments', $data);
    }
    public function contributions()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Fetch contributions from database
        $contributionModel = new ContributionModel();
        $allContributions = $contributionModel->findAll();

        // Calculate counts
        $activeCount = 0;
        $inactiveCount = 0;
        $totalCount = count($allContributions);

        foreach ($allContributions as $contrib) {
            if ($contrib['status'] === 'active') {
                $activeCount++;
            } else {
                $inactiveCount++;
            }
        }

        // Sort contributions: active first, then by date
        usort($allContributions, function($a, $b) {
            // First sort by status (active first)
            if ($a['status'] === $b['status']) {
                // If same status, sort by created_at (most recent first)
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            return $a['status'] === 'active' ? -1 : 1;
        });

        // Example: pass session data to the view
        $data = [
            'title' => 'Contributions',
            'pageTitle' => 'Contributions',
            'pageSubtitle' => 'Manage contributions and donations',
            'username' => session()->get('username'),
            'contributions' => $allContributions,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'totalCount' => $totalCount,
        ];

        return view('admin/contributions', $data);
    }
    
    public function partialPayments()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Fetch contributions for the modal dropdown
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // Example: pass session data to the view
        $data = [
            'title' => 'Partial Payments',
            'pageTitle' => 'Partial Payments',
            'pageSubtitle' => 'Manage partial payments and transactions',
            'username' => session()->get('username'),
            'contributions' => $contributions,
        ];

        return view('admin/partial_payments', $data);
    }

    public function history()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Fetch contributions for the modal dropdown
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // Example: pass session data to the view
        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View payment history and details',
            'username' => session()->get('username'),
            'contributions' => $contributions,
        ];

        return view('admin/history', $data);
    }

    public function analytics()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Analytics',
            'pageTitle' => 'Analytics',
            'pageSubtitle' => 'View analytics and reports',
            'username' => session()->get('username'),
        ];

        return view('admin/analytics', $data);
    }

    public function payers()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Load models
        $payerModel = new \App\Models\PayerModel();
        $paymentModel = new \App\Models\PaymentModel();
        
        // Fetch all payers with payment statistics
        $payers = $payerModel->findAll();
        $payments = $paymentModel->findAll();
        
        // Calculate statistics for each payer
        $payersWithStats = [];
        foreach ($payers as $payer) {
            // Get all payments for this payer
            $payerPayments = $paymentModel->where('payer_id', $payer['id'])->findAll();
            
            $totalPaid = 0;
            $lastPaymentDate = null;
            
            foreach ($payerPayments as $payment) {
                $totalPaid += $payment['amount_paid'];
                if (!$lastPaymentDate || strtotime($payment['payment_date']) > strtotime($lastPaymentDate)) {
                    $lastPaymentDate = $payment['payment_date'];
                }
            }
            
            // Get computed payment status (fully paid, partial, or unpaid)
            $paymentStatus = $paymentModel->getPaymentStatus($payer['id']);
            
            // Determine activity status based on last payment date (within last 30 days = active)
            $activityStatus = 'inactive';
            if ($lastPaymentDate) {
                $daysSinceLastPayment = (time() - strtotime($lastPaymentDate)) / (60 * 60 * 24);
                if ($daysSinceLastPayment <= 30) {
                    $activityStatus = 'active';
                } elseif ($daysSinceLastPayment <= 90) {
                    $activityStatus = 'pending';
                }
            }
            
            $payersWithStats[] = [
                'id' => $payer['id'],
                'payer_id' => $payer['payer_id'],
                'payer_name' => $payer['payer_name'],
                'email_address' => $payer['email_address'],
                'contact_number' => $payer['contact_number'],
                'course_department' => $payer['course_department'] ?? null,
                'profile_picture' => $payer['profile_picture'] ?? null,
                'total_payments' => count($payerPayments),
                'total_paid' => $totalPaid,
                'last_payment' => $lastPaymentDate,
                'payment_status' => $paymentStatus, // Computed: fully paid, partial, unpaid
                'status' => $activityStatus // Activity: active, pending, inactive
            ];
        }
        
        // Calculate overall statistics
        $totalPayers = count($payersWithStats);
        $activePayers = count(array_filter($payersWithStats, fn($p) => $p['status'] === 'active'));
        $totalAmount = array_sum(array_column($payersWithStats, 'total_paid'));
        $avgPaymentPerStudent = $totalPayers > 0 ? $totalAmount / $totalPayers : 0;
        
        $payerStats = [
            'total_payers' => $totalPayers,
            'active_payers' => $activePayers,
            'total_amount' => $totalAmount,
            'avg_payment_per_student' => $avgPaymentPerStudent
        ];

        // Example: pass session data to the view
        $data = [
            'title' => 'Students Management',
            'pageTitle' => 'Payers',
            'pageSubtitle' => 'Manage student records and information',
            'username' => session()->get('username'),
            'payers' => $payersWithStats,
            'payerStats' => $payerStats
        ];

        return view('admin/payers', $data);
    }
    
    public function savePayer()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }
        
        try {
            $payerModel = new \App\Models\PayerModel();
            
            // Get form data
            $data = [
                'payer_id' => trim($this->request->getPost('payer_id')),
                'payer_name' => trim($this->request->getPost('payer_name')),
                'contact_number' => trim($this->request->getPost('contact_number')),
                'email_address' => trim($this->request->getPost('email_address')),
                'course_department' => trim($this->request->getPost('course_department'))
            ];
            
            // Validate required fields
            if (empty($data['payer_id']) || empty($data['payer_name']) || empty($data['email_address'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Student ID, Name, and Email Address are required'
                ]);
            }
            
            // Check if payer_id already exists
            $existingPayer = $payerModel->where('payer_id', $data['payer_id'])->first();
            if ($existingPayer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'A payer with this Student ID already exists'
                ]);
            }
            
            // Validate and sanitize phone number if provided
            if (!empty($data['contact_number'])) {
                // Sanitize phone number (remove non-numeric characters)
                $data['contact_number'] = sanitize_phone_number($data['contact_number']);
                
                // Validate phone number format (must be exactly 11 digits)
                if (!validate_phone_number($data['contact_number'])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Contact number must be exactly 11 digits (numbers only)'
                    ]);
                }
            }
            
            // Validate email format (required)
            if (!filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid email address format'
                ]);
            }
            
            // Check if email already exists
            $existingEmail = $payerModel->where('email_address', $data['email_address'])->first();
            if ($existingEmail) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'A payer with this email address already exists'
                ]);
            }
            
            // Save to database
            $result = $payerModel->insert($data);
            
            if ($result) {
                // Log user activity
                $this->logUserActivity('create', 'payer', $result, 'Added new payer: ' . $data['payer_name']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payer added successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to add payer'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getPayer($payerId)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }
        
        try {
            $payerModel = new \App\Models\PayerModel();
            $payer = $payerModel->find($payerId);
            
            if (!$payer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer not found'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'payer' => $payer
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getPayerDetails($payerId)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }
        
        try {
            $payerModel = new \App\Models\PayerModel();
            $paymentModel = new \App\Models\PaymentModel();
            
            // Get payer data
            $payer = $payerModel->find($payerId);
            
            if (!$payer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer not found'
                ]);
            }
            
            // Get all payments for this payer
            $payments = $paymentModel
                ->select('payments.*, contributions.title as contribution_title')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->where('payments.payer_id', $payerId)
                ->orderBy('payments.payment_date', 'DESC')
                ->findAll();
            
            // Add computed status to each payment
            foreach ($payments as &$payment) {
                $contributionId = $payment['contribution_id'] ?? null;
                $payment['computed_status'] = $paymentModel->getPaymentStatus($payerId, $contributionId);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'payer' => $payer,
                'payments' => $payments
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function updatePayer($payerId)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }
        
        try {
            $payerModel = new \App\Models\PayerModel();
            
            // Check if payer exists
            $payer = $payerModel->find($payerId);
            if (!$payer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer not found'
                ]);
            }
            
            // Get form data
            $data = [
                'payer_name' => $this->request->getPost('payer_name'),
                'contact_number' => $this->request->getPost('contact_number'),
                'email_address' => $this->request->getPost('email_address')
            ];
            
            // Validate required fields
            if (empty($data['payer_name'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer name is required'
                ]);
            }
            
            // Store old values for activity logging
            $oldName = $payer['payer_name'];
            $oldContact = $payer['contact_number'];
            $oldEmail = $payer['email_address'];
            
            // Build activity description with changes
            $changes = [];
            if ($data['payer_name'] !== $oldName) {
                $changes[] = "Name: {$oldName} → {$data['payer_name']}";
            }
            if ($data['contact_number'] !== $oldContact) {
                $changes[] = "Contact: {$oldContact} → {$data['contact_number']}";
            }
            if ($data['email_address'] !== $oldEmail) {
                $changes[] = "Email: {$oldEmail} → {$data['email_address']}";
            }
            
            // Update payer
            $result = $payerModel->update($payerId, $data);
            
            if ($result) {
                // Log user activity with change details
                $description = !empty($changes) ? 'Updated payer (' . implode(', ', $changes) . ')' : 'Updated payer: ' . $data['payer_name'];
                $this->logUserActivity('update', 'payer', $payerId, $description);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payer updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update payer'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function exportPayerPDF($payerId)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }
        
        try {
            $payerModel = new \App\Models\PayerModel();
            $paymentModel = new \App\Models\PaymentModel();
            $contributionModel = new \App\Models\ContributionModel();
            
            // Get payer data
            $payer = $payerModel->find($payerId);
            
            if (!$payer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer not found'
                ]);
            }
            
            // Get all payments for this payer
            $payments = $paymentModel
                ->select('payments.*, contributions.title as contribution_title, contributions.amount as contribution_amount')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->where('payments.payer_id', $payerId)
                ->orderBy('payments.payment_date', 'DESC')
                ->findAll();
            
            // Calculate totals
            $totalPaid = array_sum(array_column($payments, 'amount_paid'));
            $totalPayments = count($payments);
            
            // Load TCPDF library
            require_once ROOTPATH . 'vendor/autoload.php';
            
            // Create new PDF document
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('ClearPay System');
            $pdf->SetAuthor('ClearPay');
            $pdf->SetTitle('Payer Details - ' . $payer['payer_name']);
            $pdf->SetSubject('Payment Records');
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            
            // Add a page
            $pdf->AddPage();
            
            // Set font
            $pdf->SetFont('helvetica', '', 10);
            
            // Header Section
            $pdf->SetFillColor(52, 152, 219);
            $pdf->Rect(0, 0, 210, 50, 'F');
            
            // Logo and Title
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 24);
            $pdf->SetXY(15, 8);
            $pdf->Cell(0, 10, 'ClearPay', 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetXY(15, 18);
            $pdf->Cell(0, 8, 'Payment Records & Student Details', 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetXY(15, 25);
            $pdf->Cell(0, 6, date('F j, Y g:i A'), 0, 1, 'L');
            
            // Reset text color
            $pdf->SetTextColor(0, 0, 0);
            
            // Payer Information Section
            $pdf->SetY(60);
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Student Information', 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(245, 245, 245);
            
            // Payer Details Table
            $pdf->Cell(95, 8, 'Student ID:', 1, 0, 'L', true);
            $pdf->Cell(95, 8, $payer['payer_id'], 1, 1, 'L');
            
            $pdf->Cell(95, 8, 'Full Name:', 1, 0, 'L', true);
            $pdf->Cell(95, 8, $payer['payer_name'], 1, 1, 'L');
            
            $pdf->Cell(95, 8, 'Email Address:', 1, 0, 'L', true);
            $pdf->Cell(95, 8, $payer['email_address'] ?? 'N/A', 1, 1, 'L');
            
            $pdf->Cell(95, 8, 'Contact Number:', 1, 0, 'L', true);
            $pdf->Cell(95, 8, $payer['contact_number'] ?? 'N/A', 1, 1, 'L');
            
            // Summary Statistics
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Payment Summary', 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(245, 245, 245);
            
            $pdf->Cell(95, 8, 'Total Payments:', 1, 0, 'L', true);
            $pdf->Cell(95, 8, number_format($totalPayments), 1, 1, 'L');
            
            $pdf->Cell(95, 8, 'Total Amount Paid:', 1, 0, 'L', true);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(95, 8, 'PHP ' . number_format($totalPaid, 2), 1, 1, 'L');
            
            // Payment History Table
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Payment History', 0, 1, 'L');
            
            // Table Header
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(52, 152, 219);
            $pdf->SetTextColor(255, 255, 255);
            
            $pdf->Cell(30, 8, 'Date', 1, 0, 'C', true);
            $pdf->Cell(75, 8, 'Contribution', 1, 0, 'L', true);
            $pdf->Cell(40, 8, 'Amount', 1, 0, 'R', true);
            $pdf->Cell(45, 8, 'Method', 1, 1, 'C', true);
            
            // Table Data
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            
            $fill = false;
            foreach ($payments as $payment) {
                $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
                
                $paymentDate = date('M j, Y', strtotime($payment['payment_date']));
                $contribution = substr($payment['contribution_title'], 0, 50);
                if (strlen($payment['contribution_title']) > 50) {
                    $contribution .= '...';
                }
                $amount = 'PHP ' . number_format($payment['amount_paid'], 2);
                $method = $payment['payment_method'] ?? 'N/A';
                
                $pdf->Cell(30, 7, $paymentDate, 1, 0, 'C', $fill);
                $pdf->Cell(75, 7, $contribution, 1, 0, 'L', $fill);
                $pdf->Cell(40, 7, $amount, 1, 0, 'R', $fill);
                $pdf->Cell(45, 7, $method, 1, 1, 'C', $fill);
                
                $fill = !$fill;
            }
            
            // Footer
            $pdf->SetY(-15);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->Cell(0, 10, 'Generated on ' . date('F j, Y \a\t g:i A') . ' - ClearPay Payment Management System', 0, 0, 'C');
            
            // Close and output PDF document
            $filename = 'Payer_' . str_replace(' ', '_', $payer['payer_name']) . '_' . date('YmdHis') . '.pdf';
            
            $pdf->Output($filename, 'D'); // 'D' for download
            
        } catch (\Exception $e) {
            log_message('error', 'PDF Export Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ]);
        }
    }
    
    public function announcements()
    {
        // Redirect to the AnnouncementsController
        return redirect()->to('/announcements/index');
    }

    public function profile()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Fetch user data
        $userModel = new UserModel();
        $user = $userModel->where('id', session()->get('user-id'))->first();

        // Example: pass session data to the view
        $data = [
            'title' => 'Profile',
            'pageTitle' => 'Profile',
            'pageSubtitle' => 'View and edit your profile',
            'username' => session()->get('username'),
            'user' => $user
        ];

        return view('admin/profile', $data);
    }

    public function update()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $userModel = new UserModel();
        $userId = session()->get('user-id');
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        // Prepare update data
        $updateData = [];

        // Update name if provided
        if ($this->request->getPost('name')) {
            $updateData['name'] = $this->request->getPost('name');
        }

        // Update email if provided
        if ($this->request->getPost('email')) {
            $updateData['email'] = $this->request->getPost('email');
        }

        // Update phone if provided
        if ($this->request->getPost('phone')) {
            $updateData['phone'] = $this->request->getPost('phone');
        }

        // Handle password change
        if ($this->request->getPost('change_password') == '1') {
            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]);
            }

            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Handle profile picture upload
        $profilePicFile = $this->request->getFile('profile_picture');
        if ($profilePicFile && $profilePicFile->isValid() && !$profilePicFile->hasMoved()) {
            $newName = $profilePicFile->getRandomName();
            
            // Store in both writable and public directories
            $writablePath = WRITEPATH . 'uploads/profile/';
            $publicPath = FCPATH . 'uploads/profile/';
            
            // Create directories if they don't exist
            if (!is_dir($writablePath)) {
                mkdir($writablePath, 0777, true);
            }
            if (!is_dir($publicPath)) {
                mkdir($publicPath, 0777, true);
            }

            // Move to writable first
            if ($profilePicFile->move($writablePath, $newName)) {
                // Copy to public directory for web access
                copy($writablePath . $newName, $publicPath . $newName);
                $updateData['profile_picture'] = 'uploads/profile/' . $newName;
            }
        }

        // Update user data
        if (!empty($updateData)) {
            $userModel->update($userId, $updateData);
        }

        // Update session data
        if (isset($updateData['name'])) {
            session()->set('name', $updateData['name']);
        }
        if (isset($updateData['email'])) {
            session()->set('email', $updateData['email']);
        }
        if (isset($updateData['profile_picture'])) {
            session()->set('profile_picture', $updateData['profile_picture']);
        }

        // Get updated user data
        $updatedUser = $userModel->find($userId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $updatedUser,
            'profile_picture_url' => $updatedUser['profile_picture'] ? base_url($updatedUser['profile_picture']) : null
        ]);
    }

    public function settings()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Load PaymentMethodModel to get payment methods data
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->orderBy('name', 'ASC')->findAll();

        // Example: pass session data to the view
        $data = [
            'title' => 'Settings',
            'pageTitle' => 'Settings',
            'pageSubtitle' => 'Manage your account settings',
            'username' => session()->get('username'),
            'paymentMethods' => $paymentMethods,
        ];

        return view('admin/settings', $data);
    }

    private function logUserActivity($activityType, $entityType, $entityId, $description)
    {
        try {
            $db = \Config\Database::connect();
            
            $data = [
                'user_id' => session()->get('user-id'),
                'activity_type' => $activityType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->table('user_activities')->insert($data);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            log_message('error', 'Failed to log user activity: ' . $e->getMessage());
        }
    }
}
