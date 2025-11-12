<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RefundModel;
use App\Models\PaymentModel;
use App\Models\PayerModel;
use App\Models\ContributionModel;
use App\Models\UserModel;
use App\Models\RefundMethodModel;
use App\Services\ActivityLogger;

class RefundsController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $refundModel = new RefundModel();
        $paymentModel = new PaymentModel();
        $refundMethodModel = new RefundMethodModel();

        // Get refund statistics
        $stats = $refundModel->getStats();

        // Get pending refund requests (from payers)
        $pendingRequests = $refundModel->getPendingRequests();

        // Get refund history
        $refundHistory = $refundModel->getRefundHistory(null, 100);

        // Get all payments for refund processing (recently paid, not fully refunded)
        $recentPayments = $paymentModel
            ->select('payments.*, payers.payer_name, payers.payer_id as payer_student_id, contributions.title as contribution_title')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.deleted_at', null)
            ->where('payments.payment_status', 'fully paid')
            ->orderBy('payments.payment_date', 'DESC')
            ->limit(50)
            ->findAll();

        // Get grouped payments for group refund option
        $groupedPayments = $paymentModel->getGroupedPayments();

        // Get active refund methods
        $refundMethods = $refundMethodModel->getActiveMethods();

        $data = [
            'title' => 'Refunds Management',
            'pageTitle' => 'Refunds',
            'pageSubtitle' => 'Manage refunds, process refunds, and view refund history',
            'username' => session()->get('username'),
            'stats' => $stats,
            'pendingRequests' => $pendingRequests,
            'refundHistory' => $refundHistory,
            'recentPayments' => $recentPayments,
            'groupedPayments' => $groupedPayments,
            'refundMethods' => $refundMethods
        ];

        return view('admin/refunds', $data);
    }

    /**
     * Process a refund (admin initiated)
     * Supports: custom (single payment), group (all payments in sequence), sequence (specific payments)
     */
    public function processRefund()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(405);
        }

        $refundType = $this->request->getPost('refund_type'); // 'group', 'sequence'
        
        // Get refund method codes from database for validation
        $refundMethodModel = new RefundMethodModel();
        $validRefundMethodCodes = $refundMethodModel->getAllCodes();
        // Also allow 'original_method' as a special case
        $validRefundMethodCodes[] = 'original_method';
        $validRefundMethodCodes = array_unique($validRefundMethodCodes);
        
        // Since we're using VARCHAR now, we'll validate against the list manually
        $rules = [
            'refund_type' => 'required|in_list[group,sequence]',
            'payer_id' => 'required|integer',
            'contribution_id' => 'required|integer',
            'refund_amount' => 'required|decimal',
            'refund_reason' => 'permit_empty|string',
            'refund_method' => 'required|alpha_dash|max_length[50]',
            'refund_reference' => 'permit_empty|string|max_length[100]'
        ];

        // Group and sequence refunds require payment_sequence
        $rules['payment_sequence'] = 'required|integer';
        if ($refundType === 'sequence') {
            $rules['payment_ids'] = 'required'; // JSON array of payment IDs
        }

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $paymentModel = new PaymentModel();
        $refundModel = new RefundModel();
        $userId = session()->get('user-id');
        $payerId = $this->request->getPost('payer_id');
        $contributionId = $this->request->getPost('contribution_id');
        $refundAmount = (float)$this->request->getPost('refund_amount');
        $refundMethod = $this->request->getPost('refund_method');
        $refundReason = $this->request->getPost('refund_reason');
        $refundReference = $this->request->getPost('refund_reference');
        $adminNotes = $this->request->getPost('admin_notes');

        // Validate refund method against database (must be in refund_methods table or 'original_method')
        if (!in_array($refundMethod, $validRefundMethodCodes)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'refund_method' => 'The selected refund method is not valid. Please select from available refund methods.'
                ]
            ]);
        }

        $refundedPayments = [];
        $totalRefunded = 0;
        $refundIds = [];

        try {
            if ($refundType === 'group') {
                // Group refund - refund all payments in a payment_sequence
                $paymentSequence = $this->request->getPost('payment_sequence');
                $payments = $paymentModel->getPaymentsByPayerAndContribution($payerId, $contributionId, $paymentSequence);
                
                if (empty($payments) || empty($payments[0]['payments'])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No payments found for this group'
                    ]);
                }

                $groupPayments = $payments[0]['payments'];
                $groupTotal = $payments[0]['total_amount'];
                
                // Validate refund amount
                $availableGroupAmount = $this->getAvailableGroupRefundAmount($payerId, $contributionId, $paymentSequence);
                if ($refundAmount > $availableGroupAmount) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Refund amount cannot exceed available group amount (₱" . number_format($availableGroupAmount, 2) . ")"
                    ]);
                }

                // Check for existing refunds in group
                foreach ($groupPayments as $groupPayment) {
                    $existingRefund = $refundModel
                        ->where('payment_id', $groupPayment['id'])
                        ->whereIn('status', ['pending', 'processing'])
                        ->first();
                    
                    if ($existingRefund) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => "Payment #{$groupPayment['id']} already has a pending or processing refund"
                        ]);
                    }
                }

                // Distribute refund amount proportionally across payments
                $remainingRefund = $refundAmount;
                foreach ($groupPayments as $index => $groupPayment) {
                    if ($remainingRefund <= 0) break;
                    
                    $paymentAvailable = $this->getAvailableRefundAmount($groupPayment['id']);
                    if ($paymentAvailable <= 0) continue;

                    // Calculate proportional amount (last payment gets remainder)
                    if ($index === count($groupPayments) - 1) {
                        $paymentRefundAmount = $remainingRefund;
                    } else {
                        $proportion = (float)$groupPayment['amount_paid'] / $groupTotal;
                        $paymentRefundAmount = min($remainingRefund, $paymentAvailable, $proportion * $refundAmount);
                    }

                    if ($paymentRefundAmount > 0) {
                        // Determine final refund method
                        // If 'original_method' is selected, use the payment's original method and find matching refund method
                        if ($refundMethod === 'original_method') {
                            $paymentMethod = $groupPayment['payment_method'] ?? 'cash';
                            // Map payment methods to refund method codes
                            $methodMapping = [
                                'cash' => 'cash',
                                'online' => 'bank_transfer',
                                'bank' => 'bank_transfer',
                                'check' => 'bank_transfer',
                                'bank_transfer' => 'bank_transfer',
                                'gcash' => 'gcash',
                                'paymaya' => 'paymaya'
                            ];
                            $finalRefundMethod = $methodMapping[$paymentMethod] ?? 'cash';
                        } else {
                            // Use the selected refund method code directly (now that we support VARCHAR)
                            $finalRefundMethod = $refundMethod;
                        }
                        
                        // Final validation - ensure it's a valid refund method code
                        if (!in_array($finalRefundMethod, $validRefundMethodCodes)) {
                            log_message('warning', 'Invalid refund method code: ' . $finalRefundMethod . ', defaulting to cash');
                            $finalRefundMethod = 'cash';
                        }
                        
                        $refundData = [
                            'payment_id' => $groupPayment['id'],
                            'payer_id' => $payerId,
                            'contribution_id' => $contributionId,
                            'refund_amount' => round($paymentRefundAmount, 2),
                            'refund_reason' => $refundReason ? ($refundReason . " (Group refund for sequence #{$paymentSequence})") : "Group refund for sequence #{$paymentSequence}",
                            'refund_method' => $finalRefundMethod,
                            'refund_reference' => $refundReference ?: null,
                            'status' => 'completed', // Admin-initiated refunds are immediately completed
                            'request_type' => 'admin_initiated',
                            'requested_by_payer' => 0,
                            'processed_by' => $userId,
                            'admin_notes' => $adminNotes ?: null,
                            'processed_at' => date('Y-m-d H:i:s')
                        ];

                        $refundId = $refundModel->insert($refundData);
                        if (!$refundId) {
                            $errors = $refundModel->errors();
                            log_message('error', 'Failed to insert refund. Payment ID: ' . $groupPayment['id']);
                            log_message('error', 'Refund Data: ' . json_encode($refundData));
                            log_message('error', 'Validation Errors: ' . json_encode($errors));
                            throw new \Exception('Failed to insert refund record. Validation errors: ' . implode(', ', $errors ?: ['Unknown error']));
                        }
                        
                        $refundIds[] = $refundId;
                        $totalRefunded += round($paymentRefundAmount, 2);
                        $refundedPayments[] = $groupPayment['id'];
                        $remainingRefund -= round($paymentRefundAmount, 2);
                    }
                }

            } elseif ($refundType === 'sequence') {
                // Custom sequence refund - refund specific payments from a sequence
                $paymentIds = json_decode($this->request->getPost('payment_ids'), true);
                $paymentSequence = $this->request->getPost('payment_sequence');
                
                if (empty($paymentIds) || !is_array($paymentIds)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid payment IDs'
                    ]);
                }

                // Get payments
                $payments = $paymentModel
                    ->whereIn('id', $paymentIds)
                    ->where('payer_id', $payerId)
                    ->where('contribution_id', $contributionId)
                    ->where('payment_sequence', $paymentSequence)
                    ->where('deleted_at', null)
                    ->findAll();

                if (empty($payments)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No valid payments found'
                    ]);
                }

                // Calculate total available
                $totalAvailable = 0;
                foreach ($payments as $payment) {
                    $totalAvailable += $this->getAvailableRefundAmount($payment['id']);
                    
                    // Check for existing refunds
                    $existingRefund = $refundModel
                        ->where('payment_id', $payment['id'])
                        ->whereIn('status', ['pending', 'processing'])
                        ->first();
                    
                    if ($existingRefund) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => "Payment #{$payment['id']} already has a pending or processing refund"
                        ]);
                    }
                }

                if ($refundAmount > $totalAvailable) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Refund amount cannot exceed available amount (₱" . number_format($totalAvailable, 2) . ")"
                    ]);
                }

                // Distribute refund proportionally
                $sequenceTotal = array_sum(array_column($payments, 'amount_paid'));
                $remainingRefund = $refundAmount;
                
                foreach ($payments as $index => $payment) {
                    if ($remainingRefund <= 0) break;
                    
                    $paymentAvailable = $this->getAvailableRefundAmount($payment['id']);
                    if ($paymentAvailable <= 0) continue;

                    if ($index === count($payments) - 1) {
                        $paymentRefundAmount = $remainingRefund;
                    } else {
                        $proportion = (float)$payment['amount_paid'] / $sequenceTotal;
                        $paymentRefundAmount = min($remainingRefund, $paymentAvailable, $proportion * $refundAmount);
                    }

                    if ($paymentRefundAmount > 0) {
                        // Determine final refund method
                        // If 'original_method' is selected, use the payment's original method and find matching refund method
                        if ($refundMethod === 'original_method') {
                            $paymentMethod = $payment['payment_method'] ?? 'cash';
                            // Map payment methods to refund method codes
                            $methodMapping = [
                                'cash' => 'cash',
                                'online' => 'bank_transfer',
                                'bank' => 'bank_transfer',
                                'check' => 'bank_transfer',
                                'bank_transfer' => 'bank_transfer',
                                'gcash' => 'gcash',
                                'paymaya' => 'paymaya'
                            ];
                            $finalRefundMethod = $methodMapping[$paymentMethod] ?? 'cash';
                        } else {
                            // Use the selected refund method code directly (now that we support VARCHAR)
                            $finalRefundMethod = $refundMethod;
                        }
                        
                        // Final validation - ensure it's a valid refund method code
                        if (!in_array($finalRefundMethod, $validRefundMethodCodes)) {
                            log_message('warning', 'Invalid refund method code: ' . $finalRefundMethod . ', defaulting to cash');
                            $finalRefundMethod = 'cash';
                        }
                        
                        $refundData = [
                            'payment_id' => $payment['id'],
                            'payer_id' => $payerId,
                            'contribution_id' => $contributionId,
                            'refund_amount' => round($paymentRefundAmount, 2),
                            'refund_reason' => $refundReason ? ($refundReason . " (Sequence refund)") : "Sequence refund",
                            'refund_method' => $finalRefundMethod,
                            'refund_reference' => $refundReference ?: null,
                            'status' => 'completed', // Admin-initiated refunds are immediately completed
                            'request_type' => 'admin_initiated',
                            'requested_by_payer' => 0,
                            'processed_by' => $userId,
                            'admin_notes' => $adminNotes ?: null,
                            'processed_at' => date('Y-m-d H:i:s')
                        ];

                        $refundId = $refundModel->insert($refundData);
                        if (!$refundId) {
                            $errors = $refundModel->errors();
                            log_message('error', 'Failed to insert refund. Payment ID: ' . $payment['id']);
                            log_message('error', 'Refund Data: ' . json_encode($refundData));
                            log_message('error', 'Validation Errors: ' . json_encode($errors));
                            throw new \Exception('Failed to insert refund record. Validation errors: ' . implode(', ', $errors ?: ['Unknown error']));
                        }
                        
                        $refundIds[] = $refundId;
                        $totalRefunded += round($paymentRefundAmount, 2);
                        $refundedPayments[] = $payment['id'];
                        $remainingRefund -= round($paymentRefundAmount, 2);
                    }
                }
            }

            if (empty($refundIds)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to process refund'
                ]);
            }

            // Log activity with admin name
            $activityLogger = new ActivityLogger();
            $userModel = new \App\Models\UserModel();
            $admin = $userModel->find($userId);
            $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
            
            // Get the first refund to log (representative of the batch)
            $firstRefund = $refundModel->find($refundIds[0]);
            if ($firstRefund) {
                $firstRefund['refund_amount'] = $totalRefunded; // Override with total for logging
                $activityLogger->logRefund('processed', $firstRefund, $adminName);
            }

            // Send emails for all processed refunds
            foreach ($refundIds as $refundId) {
                try {
                    // Get refund with full details (including payer email)
                    $refundDetails = $refundModel->select('
                        refunds.*,
                        payments.amount_paid,
                        payments.payment_method as original_payment_method,
                        payments.receipt_number,
                        payments.payment_date,
                        payers.payer_name,
                        payers.payer_id as payer_student_id,
                        payers.contact_number,
                        payers.email_address,
                        payers.profile_picture,
                        contributions.title as contribution_title,
                        contributions.description as contribution_description
                    ')
                    ->join('payments', 'payments.id = refunds.payment_id', 'left')
                    ->join('payers', 'payers.id = refunds.payer_id', 'left')
                    ->join('contributions', 'contributions.id = refunds.contribution_id', 'left')
                    ->where('refunds.id', $refundId)
                    ->first();

                    // Send email to payer if email address is available
                    if ($refundDetails && !empty($refundDetails['email_address'])) {
                        $this->sendRefundApprovalEmail($refundDetails, $adminNotes, $refundReference);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to send refund approval email for refund ID ' . $refundId . ': ' . $e->getMessage());
                    // Continue processing other refunds even if one email fails
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Refund processed successfully',
                'refund_ids' => $refundIds,
                'total_refunded' => $totalRefunded,
                'payment_count' => count($refundedPayments)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Refund processing error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while processing the refund: ' . $e->getMessage(),
                'debug' => [
                    'refund_type' => $refundType ?? 'unknown',
                    'payer_id' => $payerId ?? null,
                    'contribution_id' => $contributionId ?? null,
                    'refund_amount' => $refundAmount ?? null
                ]
            ]);
        }
    }

    /**
     * Get available refund amount for a payment
     */
    private function getAvailableRefundAmount($paymentId)
    {
        $paymentModel = new PaymentModel();
        $refundModel = new RefundModel();

        $payment = $paymentModel->find($paymentId);
        if (!$payment) {
            return 0;
        }

        $totalRefunded = $refundModel
            ->selectSum('refund_amount')
            ->where('payment_id', $paymentId)
            ->where('status', 'completed')
            ->first();

        $refundedAmount = $totalRefunded['refund_amount'] ?? 0;
        return (float)$payment['amount_paid'] - (float)$refundedAmount;
    }

    /**
     * Get available refund amount for a payment group
     */
    private function getAvailableGroupRefundAmount($payerId, $contributionId, $paymentSequence)
    {
        $paymentModel = new PaymentModel();
        $refundModel = new RefundModel();

        $payments = $paymentModel
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contributionId)
            ->where('payment_sequence', $paymentSequence)
            ->where('deleted_at', null)
            ->findAll();

        $totalAvailable = 0;
        foreach ($payments as $payment) {
            $totalAvailable += $this->getAvailableRefundAmount($payment['id']);
        }

        return $totalAvailable;
    }

    /**
     * Get payment group details for group refund
     */
    public function getPaymentGroupDetails()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        $payerId = $this->request->getGet('payer_id');
        $contributionId = $this->request->getGet('contribution_id');
        $paymentSequence = $this->request->getGet('payment_sequence');

        if (!$payerId || !$contributionId || !$paymentSequence) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID, Contribution ID, and Payment Sequence are required'
            ]);
        }

        $paymentModel = new PaymentModel();
        $payments = $paymentModel->getPaymentsByPayerAndContribution($payerId, $contributionId, $paymentSequence);

        if (empty($payments) || empty($payments[0])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment group not found'
            ]);
        }

        $group = $payments[0];
        $availableAmount = $this->getAvailableGroupRefundAmount($payerId, $contributionId, $paymentSequence);

        // Get payer and contribution info from first payment
        $payerInfo = null;
        $contributionInfo = null;
        if (!empty($group['payments']) && !empty($group['payments'][0])) {
            $firstPayment = $group['payments'][0];
            $payerInfo = [
                'payer_name' => $firstPayment['payer_name'] ?? '',
                'payer_id' => $firstPayment['payer_id'] ?? $payerId,
                'payer_student_id' => $firstPayment['payer_student_id'] ?? ''
            ];
            $contributionInfo = [
                'contribution_title' => $firstPayment['contribution_title'] ?? '',
                'contribution_id' => $firstPayment['contribution_id'] ?? $contributionId
            ];
        }

        // Add payer and contribution info to group
        $group['payer_name'] = $payerInfo['payer_name'] ?? '';
        $group['contribution_title'] = $contributionInfo['contribution_title'] ?? '';
        $group['available_for_refund'] = $availableAmount;
        $group['total_refunded'] = $group['total_amount'] - $availableAmount;

        return $this->response->setJSON([
            'success' => true,
            'group' => $group
        ]);
    }

    /**
     * Get all payment groups for refund modal
     * Filters out fully refunded payment groups
     */
    public function getPaymentGroups()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        $paymentModel = new PaymentModel();
        $groupedPayments = $paymentModel->getGroupedPayments();

        // Filter out fully refunded payment groups
        $filteredPayments = array_filter($groupedPayments, function($group) {
            $refundStatus = $group['refund_status'] ?? 'no_refund';
            return $refundStatus !== 'fully_refunded';
        });

        return $this->response->setJSON([
            'success' => true,
            'data' => array_values($filteredPayments) // Re-index array after filtering
        ]);
    }

    /**
     * Get payment details for refund processing
     */
    public function getPaymentDetails()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        $paymentId = $this->request->getGet('payment_id');
        if (!$paymentId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment ID is required'
            ]);
        }

        $paymentModel = new PaymentModel();
        $payment = $paymentModel
            ->select('payments.*, payers.payer_name, payers.payer_id as payer_student_id, payers.contact_number, payers.email_address, contributions.title as contribution_title')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.id', $paymentId)
            ->where('payments.deleted_at', null)
            ->first();

        if (!$payment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment not found'
            ]);
        }

        // Check for existing refunds
        $refundModel = new RefundModel();
        $existingRefunds = $refundModel
            ->where('payment_id', $paymentId)
            ->findAll();

        $totalRefunded = 0;
        foreach ($existingRefunds as $refund) {
            if ($refund['status'] === 'completed') {
                $totalRefunded += (float)$refund['refund_amount'];
            }
        }

        $payment['total_refunded'] = $totalRefunded;
        $payment['available_for_refund'] = (float)$payment['amount_paid'] - $totalRefunded;

        return $this->response->setJSON([
            'success' => true,
            'payment' => $payment
        ]);
    }

    /**
     * Approve and complete a refund request from payer
     * When admin approves, it automatically processes and completes the refund
     */
    public function approveRequest()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        $refundId = $this->request->getPost('refund_id');
        if (!$refundId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund ID is required'
            ]);
        }

        $refundModel = new RefundModel();
        $refund = $refundModel->find($refundId);

        if (!$refund) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund request not found'
            ]);
        }

        if ($refund['status'] !== 'pending') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund request is not in pending status'
            ]);
        }

        $userId = session()->get('user-id');
        $adminNotes = $this->request->getPost('admin_notes');
        $refundReference = $this->request->getPost('refund_reference');

        // Get refund with full details (including payer email) before approval
        // Query the specific refund with all necessary joins
        $refundDetails = $refundModel->select('
            refunds.*,
            payments.amount_paid,
            payments.payment_method as original_payment_method,
            payments.receipt_number,
            payments.payment_date,
            payers.payer_name,
            payers.payer_id as payer_student_id,
            payers.contact_number,
            payers.email_address,
            payers.profile_picture,
            contributions.title as contribution_title,
            contributions.description as contribution_description
        ')
        ->join('payments', 'payments.id = refunds.payment_id', 'left')
        ->join('payers', 'payers.id = refunds.payer_id', 'left')
        ->join('contributions', 'contributions.id = refunds.contribution_id', 'left')
        ->where('refunds.id', $refundId)
        ->first();

        // Approve and complete the refund in one step
        // When admin approves, it means they've processed and confirmed the refund
        $refundModel->completeRefund($refundId, $userId, $adminNotes, $refundReference);

        // Log activity with admin name
        $activityLogger = new ActivityLogger();
        $userModel = new \App\Models\UserModel();
        $admin = $userModel->find($userId);
        $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
        
        // Get updated refund data (with processed_at timestamp)
        $updatedRefund = $refundModel->find($refundId);
        if ($updatedRefund) {
            $activityLogger->logRefund('completed', $updatedRefund, $adminName);
            
            // Log to user_activities table for dashboard display
            // Include refund amount and payer name
            $refundAmount = number_format($updatedRefund['refund_amount'] ?? 0, 2);
            $payerName = $refundDetails['payer_name'] ?? 'Unknown Payer';
            $this->logUserActivity('completed', 'refund', $refundId, "Refund of ₱{$refundAmount} has been processed by {$adminName} for {$payerName}");
            
            // Merge updated refund data (especially processed_at) into refund details for email
            if ($refundDetails) {
                $refundDetails['processed_at'] = $updatedRefund['processed_at'] ?? date('Y-m-d H:i:s');
            }
        }

        // Send email to payer if email address is available
        if ($refundDetails && !empty($refundDetails['email_address'])) {
            try {
                $this->sendRefundApprovalEmail($refundDetails, $adminNotes, $refundReference);
            } catch (\Exception $e) {
                log_message('error', 'Failed to send refund approval email: ' . $e->getMessage());
                // Don't fail the refund approval if email fails
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Refund request approved and completed successfully'
        ]);
    }

    /**
     * Complete a refund (mark as completed)
     */
    public function completeRefund()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        $rules = [
            'refund_id' => 'required|integer',
            'refund_reference' => 'permit_empty|string|max_length[100]',
            'admin_notes' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $refundModel = new RefundModel();
        $refund = $refundModel->find($this->request->getPost('refund_id'));

        if (!$refund) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund not found'
            ]);
        }

        $userId = session()->get('user-id');
        $refundId = $this->request->getPost('refund_id');
        $adminNotes = $this->request->getPost('admin_notes');
        $refundReference = $this->request->getPost('refund_reference');

        // Get refund with full details (including payer email) before completion
        $refundDetails = $refundModel->select('
            refunds.*,
            payments.amount_paid,
            payments.payment_method as original_payment_method,
            payments.receipt_number,
            payments.payment_date,
            payers.payer_name,
            payers.payer_id as payer_student_id,
            payers.contact_number,
            payers.email_address,
            payers.profile_picture,
            contributions.title as contribution_title,
            contributions.description as contribution_description
        ')
        ->join('payments', 'payments.id = refunds.payment_id', 'left')
        ->join('payers', 'payers.id = refunds.payer_id', 'left')
        ->join('contributions', 'contributions.id = refunds.contribution_id', 'left')
        ->where('refunds.id', $refundId)
        ->first();

        $refundModel->completeRefund(
            $refundId,
            $userId,
            $adminNotes,
            $refundReference
        );

        // Log activity with admin name
        $activityLogger = new ActivityLogger();
        $userModel = new \App\Models\UserModel();
        $admin = $userModel->find($userId);
        $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
        
        // Get updated refund data (with processed_at timestamp)
        $updatedRefund = $refundModel->find($refundId);
        if ($updatedRefund) {
            $activityLogger->logRefund('completed', $updatedRefund, $adminName);
            
            // Merge updated refund data (especially processed_at) into refund details for email
            if ($refundDetails) {
                // Log to user_activities table for dashboard display
                // Include refund amount and payer name
                $refundAmount = number_format($updatedRefund['refund_amount'] ?? 0, 2);
                $payerName = $refundDetails['payer_name'] ?? 'Unknown Payer';
                $this->logUserActivity('completed', 'refund', $refundId, "Refund of ₱{$refundAmount} has been processed by {$adminName} for {$payerName}");
                
                $refundDetails['processed_at'] = $updatedRefund['processed_at'] ?? date('Y-m-d H:i:s');
            }
        }

        // Send email to payer if email address is available
        if ($refundDetails && !empty($refundDetails['email_address'])) {
            try {
                $this->sendRefundApprovalEmail($refundDetails, $adminNotes, $refundReference);
            } catch (\Exception $e) {
                log_message('error', 'Failed to send refund approval email: ' . $e->getMessage());
                // Don't fail the refund completion if email fails
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Refund marked as completed successfully'
        ]);
    }

    /**
     * Reject a refund request
     */
    public function rejectRequest()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        $refundId = $this->request->getPost('refund_id');
        if (!$refundId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund ID is required'
            ]);
        }

        $refundModel = new RefundModel();
        $refund = $refundModel->find($refundId);

        if (!$refund) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund request not found'
            ]);
        }

        $userId = session()->get('user-id');
        $adminNotes = $this->request->getPost('admin_notes');

        $refundModel->rejectRequest($refundId, $userId, $adminNotes);

        // Get refund details for logging (with payer info)
        $refundDetails = $refundModel->select('
            refunds.*,
            payments.amount_paid,
            payments.receipt_number,
            payers.payer_name,
            payers.payer_id as payer_student_id
        ')
        ->join('payments', 'payments.id = refunds.payment_id', 'left')
        ->join('payers', 'payers.id = refunds.payer_id', 'left')
        ->where('refunds.id', $refundId)
        ->first();

        // Log activity with admin name
        $activityLogger = new ActivityLogger();
        $userModel = new \App\Models\UserModel();
        $admin = $userModel->find($userId);
        $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
        
        // Get updated refund data
        $updatedRefund = $refundModel->find($refundId);
        if ($updatedRefund) {
            $activityLogger->logRefund('rejected', $updatedRefund, $adminName);
            
            // Log to user_activities table for dashboard display
            // Include refund amount and payer name
            if ($refundDetails) {
                $refundAmount = number_format($updatedRefund['refund_amount'] ?? 0, 2);
                $payerName = $refundDetails['payer_name'] ?? 'Unknown Payer';
                $this->logUserActivity('rejected', 'refund', $refundId, "Refund of ₱{$refundAmount} rejected by {$adminName} for {$payerName}" . ($adminNotes ? " - Reason: {$adminNotes}" : ""));
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Refund request rejected'
        ]);
    }

    /**
     * Get refund request details
     */
    public function getRefundDetails()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ])->setStatusCode(401);
        }

        $refundId = $this->request->getGet('refund_id');
        if (!$refundId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund ID is required'
            ]);
        }

        // Cast refund_id to integer for proper comparison
        $refundId = (int)$refundId;

        $refundModel = new RefundModel();
        
        // More efficient: query directly by ID instead of loading all refunds
        $refundDetails = $refundModel->getRefundsWithDetails(null, null, null);
        
        // Find the specific refund
        $foundRefund = null;
        foreach ($refundDetails as $r) {
            // Ensure both are compared as integers
            if ((int)$r['id'] === $refundId) {
                $foundRefund = $r;
                break;
            }
        }

        if (!$foundRefund) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'refund' => $foundRefund
        ]);
    }

    /**
     * Send refund approval email to payer
     */
    private function sendRefundApprovalEmail($refundDetails, $adminNotes = null, $refundReference = null)
    {
        try {
            // Check if payer has an email address
            if (empty($refundDetails['email_address'])) {
                log_message('info', 'No email address for payer, skipping refund approval email');
                return false;
            }

            // Get email settings from database or config
            $emailConfig = $this->getEmailConfig();
            
            // Validate SMTP credentials
            if (empty($emailConfig['SMTPUser']) || empty($emailConfig['SMTPPass']) || empty($emailConfig['SMTPHost'])) {
                log_message('error', 'SMTP configuration incomplete for refund approval email');
                return false;
            }
            
            // Initialize email service with fresh config
            $emailService = \Config\Services::email();
            
            // Manually configure SMTP settings to ensure they're current
            $smtpConfig = [
                'protocol' => $emailConfig['protocol'] ?? 'smtp',
                'SMTPHost' => trim($emailConfig['SMTPHost'] ?? ''),
                'SMTPUser' => trim($emailConfig['SMTPUser'] ?? ''),
                'SMTPPass' => $emailConfig['SMTPPass'] ?? '', // Don't trim password
                'SMTPPort' => (int)($emailConfig['SMTPPort'] ?? 587),
                'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
                'SMTPTimeout' => (int)($emailConfig['SMTPTimeout'] ?? 30),
                'mailType' => $emailConfig['mailType'] ?? 'html',
                'mailtype' => $emailConfig['mailType'] ?? 'html',
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
            ];
            
            $emailService->initialize($smtpConfig);
            
            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
            $emailService->setTo($refundDetails['email_address']);
            $emailService->setSubject('Refund Approved - ClearPay');
            
            // Format refund method
            $refundMethod = ucwords(str_replace('_', ' ', $refundDetails['refund_method'] ?? 'N/A'));
            
            // Format dates
            $paymentDate = $refundDetails['payment_date'] ?? date('Y-m-d H:i:s');
            $formattedPaymentDate = date('F j, Y \a\t g:i A', strtotime($paymentDate));
            
            $processedDate = $refundDetails['processed_at'] ?? date('Y-m-d H:i:s');
            $formattedProcessedDate = date('F j, Y \a\t g:i A', strtotime($processedDate));
            
            // Build email message
            $message = view('emails/refund_approved', [
                'payerName' => $refundDetails['payer_name'] ?? 'Valued Payer',
                'refundId' => $refundDetails['id'] ?? 'N/A',
                'refundAmount' => $refundDetails['refund_amount'] ?? 0,
                'refundMethod' => $refundMethod,
                'refundReference' => $refundReference ?? $refundDetails['refund_reference'] ?? null,
                'refundReason' => $refundDetails['refund_reason'] ?? null,
                'adminNotes' => $adminNotes ?? $refundDetails['admin_notes'] ?? null,
                'receiptNumber' => $refundDetails['receipt_number'] ?? 'N/A',
                'paymentDate' => $formattedPaymentDate,
                'processedDate' => $formattedProcessedDate,
                'contributionTitle' => $refundDetails['contribution_title'] ?? 'N/A',
                'amountPaid' => $refundDetails['amount_paid'] ?? 0
            ]);
            
            $emailService->setMessage($message);
            
            // Log email attempt
            log_message('info', "Attempting to send refund approval email to: {$refundDetails['email_address']} using SMTP: {$emailConfig['SMTPHost']}:{$emailConfig['SMTPPort']}");
            
            // Send email
            $result = $emailService->send();
            
            if ($result) {
                log_message('info', "Refund approval email sent successfully to: {$refundDetails['email_address']}");
                return true;
            } else {
                $error = $emailService->printDebugger(['headers', 'subject']);
                log_message('error', "Failed to send refund approval email: {$error}");
                return false;
            }
        } catch (\Exception $e) {
            // Log error but don't fail the refund approval
            log_message('error', 'Failed to send refund approval email: ' . $e->getMessage());
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        } catch (\Error $e) {
            log_message('error', 'Failed to send refund approval email (Error): ' . $e->getMessage());
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get email configuration from database or fallback to config/environment
     */
    private function getEmailConfig()
    {
        try {
            $db = \Config\Database::connect();
            
            // Try to load from database first
            if ($db->tableExists('email_settings')) {
                $settings = $db->table('email_settings')
                    ->where('is_active', true)
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if ($settings) {
                    return [
                        'fromEmail' => $settings['from_email'] ?? '',
                        'fromName' => $settings['from_name'] ?? 'ClearPay',
                        'protocol' => $settings['protocol'] ?? 'smtp',
                        'SMTPHost' => $settings['smtp_host'] ?? '',
                        'SMTPUser' => $settings['smtp_user'] ?? '',
                        'SMTPPass' => $settings['smtp_pass'] ?? '',
                        'SMTPPort' => (int)($settings['smtp_port'] ?? 587),
                        'SMTPCrypto' => $settings['smtp_crypto'] ?? 'tls',
                        'SMTPTimeout' => (int)($settings['smtp_timeout'] ?? 30),
                        'mailType' => $settings['mail_type'] ?? 'html',
                        'charset' => $settings['charset'] ?? 'UTF-8',
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('debug', 'Email settings table not found, using config: ' . $e->getMessage());
        }
        
        // Fallback to config
        $config = config('Email');
        return [
            'fromEmail' => $config->fromEmail,
            'fromName' => $config->fromName,
            'protocol' => $config->protocol,
            'SMTPHost' => $config->SMTPHost,
            'SMTPUser' => $config->SMTPUser,
            'SMTPPass' => $config->SMTPPass,
            'SMTPPort' => $config->SMTPPort,
            'SMTPCrypto' => $config->SMTPCrypto,
            'SMTPTimeout' => $config->SMTPTimeout,
            'mailType' => $config->mailType,
            'charset' => $config->charset,
        ];
    }

    /**
     * Log user activity to user_activities table for admin dashboard
     */
    private function logUserActivity($activityType, $entityType, $entityId, $description)
    {
        try {
            $db = \Config\Database::connect();
            
            $userId = session()->get('user-id') ?? 1;
            
            $data = [
                'user_id' => $userId,
                'activity_type' => $activityType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $db->table('user_activities')->insert($data);
            
            if ($result) {
                log_message('info', 'User activity logged successfully');
            } else {
                log_message('error', 'Failed to insert user activity');
            }
            
            return $result;
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            log_message('error', 'Failed to log user activity: ' . $e->getMessage());
            return false;
        }
    }
}
