<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PayerModel;
use App\Models\PaymentModel;

class PayersController extends BaseController
{
    public function create()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $validation = \Config\Services::validation();
        
        // Get JSON data from request body
        $jsonData = $this->request->getJSON(true);
        
        if (!$jsonData) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
        }
        
        // Set validation rules with custom error messages
        $validation->setRules([
            'payer_name' => 'required|min_length[2]|max_length[100]',
            'new_payer_id' => 'required|min_length[2]|max_length[50]|is_unique[payers.payer_id]',
            'payer_email' => 'required|valid_email|max_length[100]|is_unique[payers.email_address]',
            'payer_phone' => 'required|min_length[7]|max_length[20]'
        ], [
            'payer_name' => [
                'required' => 'Payer name is required',
                'min_length' => 'Payer name must be at least 2 characters',
                'max_length' => 'Payer name cannot exceed 100 characters'
            ],
            'new_payer_id' => [
                'required' => 'Payer ID is required',
                'min_length' => 'Payer ID must be at least 2 characters',
                'max_length' => 'Payer ID cannot exceed 50 characters',
                'is_unique' => 'This Payer ID already exists. Please use a different one.'
            ],
            'payer_email' => [
                'required' => 'Email address is required',
                'valid_email' => 'Please enter a valid email address',
                'max_length' => 'Email address cannot exceed 100 characters',
                'is_unique' => 'This email address is already registered. Please use a different email or select "Existing Payer" instead.'
            ],
            'payer_phone' => [
                'required' => 'Phone number is required',
                'min_length' => 'Phone number must be at least 7 digits',
                'max_length' => 'Phone number cannot exceed 20 characters'
            ]
        ]);

        if (!$validation->run($jsonData)) {
            log_message('error', 'Payer validation failed: ' . json_encode($validation->getErrors()));
            log_message('error', 'Request data: ' . json_encode($jsonData));
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
                'debug_data' => $jsonData
            ]);
        }

        try {
            $payerModel = new PayerModel();
            
            // Sanitize and validate phone number using phone_helper
            $contactNumber = !empty($jsonData['payer_phone']) ? sanitize_phone_number($jsonData['payer_phone']) : '';
            
            if (!empty($contactNumber) && !validate_phone_number($contactNumber)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contact number must be exactly 11 digits (numbers only)'
                ]);
            }
            
            $data = [
                'payer_name' => trim($jsonData['payer_name']),
                'payer_id' => trim($jsonData['new_payer_id']),
                'email_address' => trim($jsonData['payer_email']),
                'contact_number' => $contactNumber,
                'course_department' => !empty($jsonData['course_department']) ? trim($jsonData['course_department']) : null,
                'email_verified' => true,
                'verification_token' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $payerId = $payerModel->insert($data);
            
            if ($payerId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payer created successfully',
                    'payer_id' => $payerId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create payer'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error creating payer: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while creating the payer'
            ]);
        }
    }
    
    /**
     * Export payers to PDF
     */
    public function exportPDF()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        try {
            $payerModel = new PayerModel();
            $paymentModel = new PaymentModel();
            
            // Get filter parameters from query string
            $searchTerm = $this->request->getGet('search');
            $courseFilter = $this->request->getGet('course');
            $statusFilter = $this->request->getGet('status');
            
            // Start with base query
            $builder = $payerModel->select('
                payers.id,
                payers.payer_id,
                payers.payer_name,
                payers.course_department,
                payers.contact_number,
                payers.email_address
            ');
            
            // Apply search filter
            if (!empty($searchTerm)) {
                $builder->groupStart()
                    ->like('payers.payer_id', $searchTerm)
                    ->orLike('payers.payer_name', $searchTerm)
                    ->orLike('payers.email_address', $searchTerm)
                    ->groupEnd();
            }
            
            // Apply course filter
            if (!empty($courseFilter) && $courseFilter !== 'all') {
                $builder->where('payers.course_department', $courseFilter);
            }
            
            $payers = $builder->orderBy('payers.payer_name', 'ASC')->findAll();
            
            // Calculate statistics for each payer
            $payersWithStats = [];
            foreach ($payers as $payer) {
                $payerId = $payer['id'];
                
                // Get all payments for this payer
                $payerPayments = $paymentModel->where('payer_id', $payerId)->findAll();
                
                $totalPaid = 0;
                $totalPayments = count($payerPayments);
                $lastPaymentDate = null;
                
                foreach ($payerPayments as $payment) {
                    $totalPaid += floatval($payment['amount_paid']);
                    if (!$lastPaymentDate || strtotime($payment['payment_date']) > strtotime($lastPaymentDate)) {
                        $lastPaymentDate = $payment['payment_date'];
                    }
                }
                
                // Get computed payment status
                $paymentStatus = $paymentModel->getPaymentStatus($payerId);
                
                // Determine activity status based on last payment date
                $activityStatus = 'inactive';
                if ($lastPaymentDate) {
                    $daysSinceLastPayment = (time() - strtotime($lastPaymentDate)) / (60 * 60 * 24);
                    if ($daysSinceLastPayment <= 30) {
                        $activityStatus = 'active';
                    } elseif ($daysSinceLastPayment <= 90) {
                        $activityStatus = 'pending';
                    }
                }
                
                // Apply status filter if provided
                if (!empty($statusFilter) && $statusFilter !== 'all') {
                    if ($statusFilter === 'active' && $activityStatus !== 'active') {
                        continue;
                    } elseif ($statusFilter === 'pending' && $activityStatus !== 'pending') {
                        continue;
                    } elseif ($statusFilter === 'inactive' && $activityStatus !== 'inactive') {
                        continue;
                    }
                }
                
                $payersWithStats[] = [
                    'payer_id' => $payer['payer_id'],
                    'payer_name' => $payer['payer_name'],
                    'course_department' => $payer['course_department'] ?? 'N/A',
                    'contact_number' => $payer['contact_number'] ?? 'N/A',
                    'email_address' => $payer['email_address'] ?? 'N/A',
                    'total_payments' => $totalPayments,
                    'total_paid' => $totalPaid,
                    'last_payment' => $lastPaymentDate ? date('M j, Y', strtotime($lastPaymentDate)) : 'Never',
                    'payment_status' => $paymentStatus,
                    'activity_status' => $activityStatus
                ];
            }
            
            // Load TCPDF library
            if (defined('COMPOSER_PATH') && file_exists(COMPOSER_PATH)) {
                require_once COMPOSER_PATH;
            } elseif (defined('ROOTPATH')) {
                require_once ROOTPATH . 'vendor/autoload.php';
            }
            
            // Create new PDF document
            $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('ClearPay System');
            $pdf->SetAuthor('ClearPay');
            $pdf->SetTitle('Payers Report');
            $pdf->SetSubject('Payer Records');
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            
            // Add a page
            $pdf->AddPage();
            
            // Set font to dejavusans which supports UTF-8 characters including ₱
            $pdf->SetFont('dejavusans', '', 10);
            
            // Header Section
            $pdf->SetFillColor(52, 152, 219);
            $pdf->Rect(0, 0, 297, 40, 'F');
            
            // Logo and Title
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('dejavusans', 'B', 24);
            $pdf->SetXY(15, 8);
            $pdf->Cell(0, 10, 'ClearPay', 0, 1, 'L');
            
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->SetXY(15, 18);
            $pdf->Cell(0, 8, 'Payers Report', 0, 1, 'L');
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->SetXY(15, 27);
            $pdf->Cell(0, 6, date('F j, Y g:i A'), 0, 1, 'L');
            
            // Reset text color
            $pdf->SetTextColor(0, 0, 0);
            
            // Information Section
            $pdf->SetY(50);
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->Cell(0, 8, 'Report Information', 0, 1, 'L');
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(0, 6, 'Total Payers: ' . count($payersWithStats), 0, 1, 'L');
            
            // Table header
            $pdf->SetY(70);
            $pdf->SetFillColor(232, 232, 232);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->Cell(30, 8, 'Student ID', 1, 0, 'C', true);
            $pdf->Cell(50, 8, 'Name', 1, 0, 'C', true);
            $pdf->Cell(50, 8, 'Course/Dept', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Total Paid', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Payments', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Last Payment', 1, 0, 'C', true);
            $pdf->Cell(32, 8, 'Status', 1, 1, 'C', true);
            
            // Table body
            $pdf->SetFont('dejavusans', '', 8);
            foreach ($payersWithStats as $payer) {
                // Status badge color based on activity status
                if ($payer['activity_status'] === 'active') {
                    $pdf->SetFillColor(46, 204, 113);
                } elseif ($payer['activity_status'] === 'pending') {
                    $pdf->SetFillColor(241, 196, 15);
                } else {
                    $pdf->SetFillColor(192, 57, 43);
                }
                
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(30, 7, $payer['payer_id'], 1, 0, 'L');
                $pdf->Cell(50, 7, $payer['payer_name'], 1, 0, 'L');
                $pdf->Cell(50, 7, $payer['course_department'], 1, 0, 'L');
                
                // Reset fill color
                $pdf->SetFillColor(255, 255, 255);
                
                $pdf->Cell(35, 7, '₱' . number_format($payer['total_paid'], 2), 1, 0, 'R');
                $pdf->Cell(30, 7, $payer['total_payments'], 1, 0, 'C');
                $pdf->Cell(35, 7, $payer['last_payment'], 1, 0, 'C');
                
                // Status
                $pdf->SetFont('dejavusans', 'B', 8);
                $pdf->Cell(32, 7, strtoupper($payer['activity_status']), 1, 1, 'C');
                $pdf->SetFont('dejavusans', '', 8);
            }
            
            // Summary footer
            $pdf->SetY($pdf->GetY() + 5);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->SetFillColor(52, 152, 219);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 8, 'SUMMARY', 1, 1, 'C', true);
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);
            $totalPaid = array_sum(array_column($payersWithStats, 'total_paid'));
            $totalPayers = count($payersWithStats);
            $activePayers = count(array_filter($payersWithStats, function($p) { return $p['activity_status'] === 'active'; }));
            $pendingPayers = count(array_filter($payersWithStats, function($p) { return $p['activity_status'] === 'pending'; }));
            $inactivePayers = count(array_filter($payersWithStats, function($p) { return $p['activity_status'] === 'inactive'; }));
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(139, 7, 'Total Amount Collected:', 1, 0, 'L', true);
            $pdf->Cell(139, 7, '₱' . number_format($totalPaid, 2), 1, 1, 'R', true);
            
            $pdf->Cell(139, 7, 'Total Payers:', 1, 0, 'L', true);
            $pdf->Cell(139, 7, $totalPayers, 1, 1, 'R', true);
            
            $pdf->Cell(139, 7, 'Active Payers:', 1, 0, 'L', true);
            $pdf->SetTextColor(46, 204, 113);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(139, 7, $activePayers, 1, 1, 'R', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('dejavusans', '', 10);
            
            $pdf->Cell(139, 7, 'Pending Payers:', 1, 0, 'L', true);
            $pdf->SetTextColor(241, 196, 15);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(139, 7, $pendingPayers, 1, 1, 'R', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('dejavusans', '', 10);
            
            $pdf->Cell(139, 7, 'Inactive Payers:', 1, 0, 'L', true);
            $pdf->SetTextColor(192, 57, 43);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(139, 7, $inactivePayers, 1, 1, 'R', true);
            $pdf->SetTextColor(0, 0, 0);
            
            // Output PDF
            $filename = 'Payers_Report_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');
            
        } catch (\Exception $e) {
            log_message('error', 'Error exporting payers PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Export payers to CSV
     */
    public function exportCSV()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        try {
            $payerModel = new PayerModel();
            $paymentModel = new PaymentModel();
            
            // Get filter parameters from query string
            $searchTerm = $this->request->getGet('search');
            $courseFilter = $this->request->getGet('course');
            $statusFilter = $this->request->getGet('status');
            
            // Start with base query
            $builder = $payerModel->select('
                payers.id,
                payers.payer_id,
                payers.payer_name,
                payers.course_department,
                payers.contact_number,
                payers.email_address
            ');
            
            // Apply search filter
            if (!empty($searchTerm)) {
                $builder->groupStart()
                    ->like('payers.payer_id', $searchTerm)
                    ->orLike('payers.payer_name', $searchTerm)
                    ->orLike('payers.email_address', $searchTerm)
                    ->groupEnd();
            }
            
            // Apply course filter
            if (!empty($courseFilter) && $courseFilter !== 'all') {
                $builder->where('payers.course_department', $courseFilter);
            }
            
            $payers = $builder->orderBy('payers.payer_name', 'ASC')->findAll();
            
            // Calculate statistics for each payer
            $payersWithStats = [];
            foreach ($payers as $payer) {
                $payerId = $payer['id'];
                
                // Get all payments for this payer
                $payerPayments = $paymentModel->where('payer_id', $payerId)->findAll();
                
                $totalPaid = 0;
                $totalPayments = count($payerPayments);
                $lastPaymentDate = null;
                
                foreach ($payerPayments as $payment) {
                    $totalPaid += floatval($payment['amount_paid']);
                    if (!$lastPaymentDate || strtotime($payment['payment_date']) > strtotime($lastPaymentDate)) {
                        $lastPaymentDate = $payment['payment_date'];
                    }
                }
                
                // Get computed payment status
                $paymentStatus = $paymentModel->getPaymentStatus($payerId);
                
                // Determine activity status based on last payment date
                $activityStatus = 'inactive';
                if ($lastPaymentDate) {
                    $daysSinceLastPayment = (time() - strtotime($lastPaymentDate)) / (60 * 60 * 24);
                    if ($daysSinceLastPayment <= 30) {
                        $activityStatus = 'active';
                    } elseif ($daysSinceLastPayment <= 90) {
                        $activityStatus = 'pending';
                    }
                }
                
                // Apply status filter if provided
                if (!empty($statusFilter) && $statusFilter !== 'all') {
                    if ($statusFilter === 'active' && $activityStatus !== 'active') {
                        continue;
                    } elseif ($statusFilter === 'pending' && $activityStatus !== 'pending') {
                        continue;
                    } elseif ($statusFilter === 'inactive' && $activityStatus !== 'inactive') {
                        continue;
                    }
                }
                
                $payersWithStats[] = [
                    'payer_id' => $payer['payer_id'],
                    'payer_name' => $payer['payer_name'],
                    'course_department' => $payer['course_department'] ?? 'N/A',
                    'contact_number' => $payer['contact_number'] ?? 'N/A',
                    'email_address' => $payer['email_address'] ?? 'N/A',
                    'total_payments' => $totalPayments,
                    'total_paid' => $totalPaid,
                    'last_payment' => $lastPaymentDate ? date('M j, Y', strtotime($lastPaymentDate)) : 'Never',
                    'payment_status' => $paymentStatus,
                    'activity_status' => $activityStatus
                ];
            }
            
            // Set headers for CSV download
            $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="Payers_Report_' . date('Y-m-d_H-i-s') . '.csv"');
            
            // Output CSV with UTF-8 BOM for Excel compatibility
            echo "\xEF\xBB\xBF";
            
            // CSV headers
            echo "Student ID,Name,Course/Department,Contact Number,Email Address,Total Paid,Payments,Last Payment,Payment Status,Activity Status\n";
            
            // CSV rows
            foreach ($payersWithStats as $payer) {
                echo '"' . str_replace('"', '""', $payer['payer_id']) . '",';
                echo '"' . str_replace('"', '""', $payer['payer_name']) . '",';
                echo '"' . str_replace('"', '""', $payer['course_department']) . '",';
                echo '"' . str_replace('"', '""', $payer['contact_number']) . '",';
                echo '"' . str_replace('"', '""', $payer['email_address']) . '",';
                echo '₱' . number_format($payer['total_paid'], 2) . ',';
                echo $payer['total_payments'] . ',';
                echo '"' . str_replace('"', '""', $payer['last_payment']) . '",';
                echo '"' . str_replace('"', '""', $payer['payment_status']) . '",';
                echo '"' . str_replace('"', '""', $payer['activity_status']) . '"';
                echo "\n";
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error exporting payers CSV: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating CSV: ' . $e->getMessage());
        }
    }
}
