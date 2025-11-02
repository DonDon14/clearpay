<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
    protected $allowedFields = [
        'payer_id', // FK to payers table
        'contribution_id',
        'amount_paid',
        'payment_method',
        'payment_status',
        'is_partial_payment',
        'remaining_balance',
        'parent_payment_id',
        'payment_sequence',
        'reference_number',
        'receipt_number',
        'qr_receipt_path',
        'recorded_by',
        'payment_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $useTimestamps = false;

    // Validation rules
    protected $validationRules = [
        'payment_status' => 'required|in_list[fully paid,partial]'
    ];

      protected $validationMessages = [
        'payment_status' => [
            'required' => 'Payment status is required.',
            'in_list'  => 'Payment status must be either Fully Paid or Partial.'
        ]
    ];

    public function getRecentPayments($limit = 5)
    {
        return $this->select('
                    payments.id,
                    payments.payer_id,
                    payers.payer_id AS payer_student_id,
                    payers.payer_name,
                    payers.contact_number,
                    payers.email_address,
                    payments.amount_paid,
                    payments.payment_method,
                    payments.payment_status,
                    payments.payment_date,
                    payments.receipt_number,
                    payments.qr_receipt_path,
                    contributions.title AS contribution_title,
                    users.username AS recorded_by_name
                ')
                ->join('payers', 'payers.id = payments.payer_id', 'left')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->join('users', 'users.id = payments.recorded_by', 'left')
                ->orderBy('payments.payment_date', 'DESC')
                ->limit($limit)
                ->findAll();
    }

    /**
     * Calculate payment status dynamically based on total paid vs total due
     * @param int $payerId The payer ID
     * @param int|null $contributionId The contribution ID (optional, for specific contribution)
     * @return string Returns 'fully paid', 'partial', or 'unpaid'
     */
    public function getPaymentStatus($payerId, $contributionId = null)
    {
        // Get total amount paid for this payer (exclude soft-deleted payments)
        // Soft deletes automatically exclude records where deleted_at is not null
        $builder = $this->selectSum('amount_paid', 'total_paid')
            ->where('payer_id', $payerId);
            
        if ($contributionId) {
            $builder->where('contribution_id', $contributionId);
        }
        
        $result = $builder->get()->getRowArray();
        $totalPaid = $result['total_paid'] ?? 0;

        // Get total due amount
        $contributionModel = new ContributionModel();
        $totalDue = 0;
        
        if ($contributionId) {
            // For specific contribution
            $contribution = $contributionModel->find($contributionId);
            $totalDue = $contribution ? (float) $contribution['amount'] : 0;
        } else {
            // For all contributions (get sum of all active contributions)
            $contributionModel->where('status', 'active');
            $contributions = $contributionModel->findAll();
            foreach ($contributions as $contribution) {
                $totalDue += (float) $contribution['amount'];
            }
        }

        // Determine status
        if ($totalPaid == 0) {
            return 'unpaid';
        } elseif ($totalPaid >= $totalDue) {
            return 'fully paid';
        } else {
            return 'partial';
        }
    }

    /**
     * Get computed payment status for display
     * This is an alias that returns formatted status
     */
    public function getComputedStatus($payerId, $contributionId = null)
    {
        return $this->getPaymentStatus($payerId, $contributionId);
    }

    /**
     * Get refund status for a payment
     * Returns: 'no_refund', 'partially_refunded', 'fully_refunded'
     */
    public function getPaymentRefundStatus($paymentId)
    {
        $refundModel = new RefundModel();
        
        // Get all completed refunds for this payment
        $refunds = $refundModel
            ->where('payment_id', $paymentId)
            ->where('status', 'completed')
            ->findAll();
        
        if (empty($refunds)) {
            return 'no_refund';
        }
        
        $payment = $this->find($paymentId);
        if (!$payment) {
            return 'no_refund';
        }
        
        $totalRefunded = array_sum(array_column($refunds, 'refund_amount'));
        $amountPaid = (float)$payment['amount_paid'];
        
        if ($totalRefunded >= $amountPaid) {
            return 'fully_refunded';
        } else {
            return 'partially_refunded';
        }
    }

    /**
     * Get total refunded amount for a payment
     */
    public function getPaymentTotalRefunded($paymentId)
    {
        $refundModel = new RefundModel();
        
        $refunds = $refundModel
            ->selectSum('refund_amount')
            ->where('payment_id', $paymentId)
            ->where('status', 'completed')
            ->first();
        
        return (float)($refunds['refund_amount'] ?? 0);
    }

    /**
     * Get payments grouped by payer and contribution
     */
    public function getGroupedPayments()
    {
        $db = \Config\Database::connect();
        
        // First, get payment groups
        $query = $db->query("
            SELECT 
                p.payer_id,
                p.contribution_id,
                COALESCE(p.payment_sequence, 1) as payment_sequence,
                payers.payer_name,
                payers.payer_id as payer_student_id,
                payers.contact_number,
                payers.email_address,
                payers.profile_picture,
                COALESCE(contributions.title, 'Unknown Contribution') as contribution_title,
                COALESCE(contributions.description, '') as contribution_description,
                COALESCE(contributions.amount, 0) as contribution_amount,
                SUM(p.amount_paid) as total_paid,
                COUNT(DISTINCT p.id) as payment_count,
                MAX(p.payment_date) as last_payment_date,
                MIN(p.payment_date) as first_payment_date,
                CASE 
                    WHEN SUM(p.amount_paid) >= COALESCE(contributions.amount, 0) THEN 'fully paid'
                    WHEN SUM(p.amount_paid) > 0 THEN 'partial'
                    ELSE 'unpaid'
                END as computed_status,
                COALESCE(contributions.amount, 0) - SUM(p.amount_paid) as remaining_balance,
                GROUP_CONCAT(DISTINCT p.id) as payment_ids
            FROM payments p
            LEFT JOIN payers ON payers.id = p.payer_id
            LEFT JOIN contributions ON contributions.id = p.contribution_id
            WHERE p.deleted_at IS NULL
            GROUP BY p.payer_id, p.contribution_id, COALESCE(p.payment_sequence, 1)
            ORDER BY last_payment_date DESC, payers.payer_name ASC
        ");
        
        if (!$query) {
            return [];
        }
        
        $groups = $query->getResultArray();
        
        // Calculate refund status for each group
        $refundModel = new RefundModel();
        foreach ($groups as &$group) {
            $paymentIds = explode(',', $group['payment_ids']);
            $totalRefunded = 0;
            
            foreach ($paymentIds as $paymentId) {
                $paymentId = (int)trim($paymentId);
                if ($paymentId > 0) {
                    $refunds = $refundModel
                        ->selectSum('refund_amount')
                        ->where('payment_id', $paymentId)
                        ->where('status', 'completed')
                        ->first();
                    
                    $totalRefunded += (float)($refunds['refund_amount'] ?? 0);
                }
            }
            
            $group['total_refunded'] = $totalRefunded;
            $group['refund_status'] = ($totalRefunded >= (float)$group['total_paid']) ? 'fully_refunded' : 
                                      (($totalRefunded > 0) ? 'partially_refunded' : 'no_refund');
            unset($group['payment_ids']); // Remove temp field
        }
        
        return $groups;
    }

    /**
     * Get individual payments for a specific payer and contribution
     */
    public function getPaymentsByPayerAndContribution($payerId, $contributionId, $paymentSequence = null)
    {
        $builder = $this->select('
            payments.*,
            payers.payer_name,
            payers.payer_id as payer_student_id,
            payers.contact_number,
            payers.email_address,
            payers.profile_picture,
            contributions.title as contribution_title,
            contributions.description as contribution_description,
            contributions.amount as contribution_amount,
            contributions.contribution_code,
            users.username as recorded_by_name
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
        ->join('users', 'users.id = payments.recorded_by', 'left')
        ->where('payments.payer_id', $payerId)
        ->where('payments.contribution_id', $contributionId)
        ->where('payments.deleted_at', null);
        
        // Filter by payment sequence if provided
        if ($paymentSequence !== null) {
            $builder->where('payments.payment_sequence', $paymentSequence);
        }
        
        $payments = $builder->orderBy('payments.payment_sequence', 'ASC')
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();
        
        // Group payments by sequence
        $groupedPayments = [];
        foreach ($payments as $payment) {
            $sequence = $payment['payment_sequence'];
            if (!isset($groupedPayments[$sequence])) {
                $groupedPayments[$sequence] = [
                    'sequence' => $sequence,
                    'payments' => [],
                    'total_amount' => 0,
                    'payment_count' => 0,
                    'first_payment_date' => null,
                    'last_payment_date' => null
                ];
            }
            
            $groupedPayments[$sequence]['payments'][] = $payment;
            $groupedPayments[$sequence]['total_amount'] += $payment['amount_paid'];
            $groupedPayments[$sequence]['payment_count']++;
            
            if (!$groupedPayments[$sequence]['first_payment_date'] || 
                $payment['payment_date'] < $groupedPayments[$sequence]['first_payment_date']) {
                $groupedPayments[$sequence]['first_payment_date'] = $payment['payment_date'];
            }
            
            if (!$groupedPayments[$sequence]['last_payment_date'] || 
                $payment['payment_date'] > $groupedPayments[$sequence]['last_payment_date']) {
                $groupedPayments[$sequence]['last_payment_date'] = $payment['payment_date'];
            }
        }
        
        return array_values($groupedPayments);
    }
}
