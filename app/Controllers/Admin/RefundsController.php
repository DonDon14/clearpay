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

        $refundType = $this->request->getPost('refund_type'); // 'custom', 'group', 'sequence'
        
        $rules = [
            'refund_type' => 'required|in_list[custom,group,sequence]',
            'payer_id' => 'required|integer',
            'contribution_id' => 'required|integer',
            'refund_amount' => 'required|decimal',
            'refund_reason' => 'permit_empty|string',
            'refund_method' => 'required|in_list[cash,bank_transfer,gcash,paymaya,original_method]',
            'refund_reference' => 'permit_empty|string|max_length[100]'
        ];

        // Custom refund requires payment_sequence and payment_ids (all payments in sequence for custom amount)
        if ($refundType === 'custom') {
            $rules['payment_sequence'] = 'required|integer';
            $rules['payment_ids'] = 'required'; // JSON array of payment IDs
        }
        // Group and sequence refunds require payment_sequence
        if (in_array($refundType, ['group', 'sequence'])) {
            $rules['payment_sequence'] = 'required|integer';
            if ($refundType === 'sequence') {
                $rules['payment_ids'] = 'required'; // JSON array of payment IDs
            }
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
        $refundMethodModel = new RefundMethodModel();
        $userId = session()->get('user-id');
        $payerId = $this->request->getPost('payer_id');
        $contributionId = $this->request->getPost('contribution_id');
        $refundAmount = (float)$this->request->getPost('refund_amount');
        $refundMethod = $this->request->getPost('refund_method');
        $refundReason = $this->request->getPost('refund_reason');
        $refundReference = $this->request->getPost('refund_reference');
        $adminNotes = $this->request->getPost('admin_notes');

        // Validate refund method
        if (!$refundModel->validateRefundMethod($refundMethod)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid refund method selected'
            ]);
        }

        $refundedPayments = [];
        $totalRefunded = 0;
        $refundIds = [];

        try {
            if ($refundType === 'custom') {
                // Custom refund - any amount from payment sequence (e.g., ₱1, ₱20, ₱35)
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

                // Validate refund amount - can be any amount up to available
                if ($refundAmount > $totalAvailable) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Refund amount cannot exceed available amount (₱" . number_format($totalAvailable, 2) . ")"
                    ]);
                }

                // Validate minimum amount
                if ($refundAmount <= 0) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Refund amount must be greater than 0"
                    ]);
                }

                // Distribute custom refund amount proportionally across payments
                $sequenceTotal = array_sum(array_column($payments, 'amount_paid'));
                $remainingRefund = $refundAmount;
                
                foreach ($payments as $index => $payment) {
                    if ($remainingRefund <= 0) break;
                    
                    $paymentAvailable = $this->getAvailableRefundAmount($payment['id']);
                    if ($paymentAvailable <= 0) continue;

                    // Calculate proportional amount (last payment gets remainder to avoid rounding issues)
                    if ($index === count($payments) - 1) {
                        $paymentRefundAmount = min($remainingRefund, $paymentAvailable);
                    } else {
                        $proportion = (float)$payment['amount_paid'] / $sequenceTotal;
                        $paymentRefundAmount = min($remainingRefund, $paymentAvailable, $proportion * $refundAmount);
                    }

                    // Only process if amount is significant (at least 0.01)
                    if ($paymentRefundAmount >= 0.01) {
                        // Convert original_method to actual payment method
                        $paymentMethod = ($refundMethod === 'original_method') ? ($payment['payment_method'] ?? 'cash') : $refundMethod;
                        
                        // Map payment method values to valid refund method values
                        // Payment methods: cash, online, bank, check
                        // Refund methods: cash, bank_transfer, gcash, paymaya, original_method
                        $methodMapping = [
                            'cash' => 'cash',
                            'online' => 'bank_transfer',
                            'bank' => 'bank_transfer',
                            'check' => 'bank_transfer',
                            'bank_transfer' => 'bank_transfer',
                            'gcash' => 'gcash',
                            'paymaya' => 'paymaya',
                            'original_method' => 'cash' // Fallback if still original_method
                        ];
                        
                        // Ensure we have a valid refund method
                        $finalRefundMethod = $methodMapping[$paymentMethod] ?? 'cash'; // Default to cash if unknown
                        
                        // Final validation - ensure it's in the allowed list and never use 'original_method' as final value
                        $allowedMethods = ['cash', 'bank_transfer', 'gcash', 'paymaya'];
                        if (!in_array($finalRefundMethod, $allowedMethods) || $finalRefundMethod === 'original_method') {
                            log_message('warning', 'Invalid refund method detected: ' . $finalRefundMethod . ', defaulting to cash');
                            $finalRefundMethod = 'cash';
                        }
                        
                        // Log the method conversion for debugging
                        log_message('debug', 'Refund method conversion - original: ' . $refundMethod . ', payment_method: ' . ($payment['payment_method'] ?? 'null') . ', final: ' . $finalRefundMethod);
                        
                        $refundData = [
                            'payment_id' => $payment['id'],
                            'payer_id' => $payerId,
                            'contribution_id' => $contributionId,
                            'refund_amount' => round($paymentRefundAmount, 2),
                            'refund_reason' => $refundReason ? ($refundReason . " (Custom refund: ₱" . number_format($refundAmount, 2) . " from sequence #{$paymentSequence})") : "Custom refund: ₱" . number_format($refundAmount, 2) . " from sequence #{$paymentSequence}",
                            'refund_method' => $finalRefundMethod,
                            'refund_reference' => $refundReference ?: null,
                            'status' => 'completed', // Admin-initiated refunds are immediately completed
                            'request_type' => 'admin_initiated',
                            'requested_by_payer' => 0,
                            'processed_by' => $userId,
                            'admin_notes' => $adminNotes ?: null,
                            'processed_at' => date('Y-m-d H:i:s')
                        ];
                        
                        // Double-check refund_method before insert
                        if (!in_array($refundData['refund_method'], ['cash', 'bank_transfer', 'gcash', 'paymaya'])) {
                            log_message('error', 'CRITICAL: Invalid refund_method about to be inserted: ' . $refundData['refund_method']);
                            $refundData['refund_method'] = 'cash';
                        }
                        
                        log_message('debug', 'Inserting refund with data: ' . json_encode($refundData));
                        
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

            } elseif ($refundType === 'group') {
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
                        // Convert original_method to actual payment method
                        $paymentMethod = ($refundMethod === 'original_method') ? ($groupPayment['payment_method'] ?? 'cash') : $refundMethod;
                        
                        // Map payment method values to valid refund method values
                        $methodMapping = [
                            'cash' => 'cash',
                            'online' => 'bank_transfer',
                            'bank' => 'bank_transfer',
                            'check' => 'bank_transfer',
                            'bank_transfer' => 'bank_transfer',
                            'gcash' => 'gcash',
                            'paymaya' => 'paymaya',
                            'original_method' => 'cash'
                        ];
                        
                        $finalRefundMethod = $methodMapping[$paymentMethod] ?? 'cash';
                        
                        // Final validation - never use 'original_method' as final value
                        $allowedMethods = ['cash', 'bank_transfer', 'gcash', 'paymaya'];
                        if (!in_array($finalRefundMethod, $allowedMethods) || $finalRefundMethod === 'original_method') {
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
                        // Convert original_method to actual payment method
                        $paymentMethod = ($refundMethod === 'original_method') ? ($payment['payment_method'] ?? 'cash') : $refundMethod;
                        
                        // Map payment method values to valid refund method values
                        $methodMapping = [
                            'cash' => 'cash',
                            'online' => 'bank_transfer',
                            'bank' => 'bank_transfer',
                            'check' => 'bank_transfer',
                            'bank_transfer' => 'bank_transfer',
                            'gcash' => 'gcash',
                            'paymaya' => 'paymaya',
                            'original_method' => 'cash'
                        ];
                        
                        $finalRefundMethod = $methodMapping[$paymentMethod] ?? 'cash';
                        
                        // Final validation - never use 'original_method' as final value
                        $allowedMethods = ['cash', 'bank_transfer', 'gcash', 'paymaya'];
                        if (!in_array($finalRefundMethod, $allowedMethods) || $finalRefundMethod === 'original_method') {
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

            // Log activity
            $activityLogger = new ActivityLogger();
            $activityMessage = "Processed {$refundType} refund of ₱" . number_format($totalRefunded, 2);
            if ($refundType !== 'custom') {
                $activityMessage .= " for " . count($refundedPayments) . " payment(s)";
            } else {
                $activityMessage .= " for payment #{$refundedPayments[0]}";
            }

            $activityLogger->logActivity(
                $userId,
                'refund_processed',
                $activityMessage,
                null,
                $payerId
            );

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
     * Approve a refund request from payer
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

        $refundModel->approveRequest($refundId, $userId, $adminNotes);

        // Log activity
        $activityLogger = new ActivityLogger();
        $activityLogger->logActivity(
            $userId,
            'refund_approved',
            "Approved refund request #{$refundId}",
            null,
            $refund['payer_id']
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Refund request approved successfully'
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
        $refundModel->completeRefund(
            $this->request->getPost('refund_id'),
            $userId,
            $this->request->getPost('admin_notes'),
            $this->request->getPost('refund_reference')
        );

        // Log activity
        $activityLogger = new ActivityLogger();
        $activityLogger->logActivity(
            $userId,
            'refund_completed',
            "Completed refund #{$refund['id']} of ₱" . number_format((float)$refund['refund_amount'], 2),
            null,
            $refund['payer_id']
        );

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

        // Log activity
        $activityLogger = new ActivityLogger();
        $activityLogger->logActivity(
            $userId,
            'refund_rejected',
            "Rejected refund request #{$refundId}",
            null,
            $refund['payer_id']
        );

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

        $refundModel = new RefundModel();
        $refund = $refundModel->getRefundsWithDetails(null, null, null);
        
        $refundDetails = null;
        foreach ($refund as $r) {
            if ($r['id'] == $refundId) {
                $refundDetails = $r;
                break;
            }
        }

        if (!$refundDetails) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'refund' => $refundDetails
        ]);
    }
}
