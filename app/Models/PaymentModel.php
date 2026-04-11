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
        'product_id',
        'quantity',
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
                    COALESCE(contributions.title, products.title) AS contribution_title,
                    CASE WHEN payments.product_id IS NOT NULL THEN \'product\' ELSE \'contribution\' END AS item_type,
                    users.username AS recorded_by_name
                ')
                ->join('payers', 'payers.id = payments.payer_id', 'left')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->join('products', 'products.id = payments.product_id', 'left')
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
        $rows = $this->select('
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
                products.title as product_title,
                products.description as product_description,
                products.amount as product_amount
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->join('products', 'products.id = payments.product_id', 'left')
            ->where('payments.deleted_at', null)
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();

        if (empty($rows)) {
            return [];
        }

        $groups = [];
        foreach ($rows as $row) {
            $isProduct = !empty($row['product_id']);
            $groupKey = $isProduct
                ? 'product-' . $row['id']
                : 'contribution-' . $row['payer_id'] . '-' . $row['contribution_id'];

            if (!isset($groups[$groupKey])) {
                $amount = (float) ($isProduct ? ($row['product_amount'] ?? 0) : ($row['contribution_amount'] ?? 0));
                $groups[$groupKey] = [
                    'payer_id' => $row['payer_id'],
                    'contribution_id' => $row['contribution_id'],
                    'product_id' => $row['product_id'],
                    'item_type' => $isProduct ? 'product' : 'contribution',
                    'payment_sequence' => $isProduct ? null : 1,
                    'payer_name' => $row['payer_name'],
                    'payer_student_id' => $row['payer_student_id'],
                    'contact_number' => $row['contact_number'],
                    'email_address' => $row['email_address'],
                    'profile_picture' => $row['profile_picture'],
                    'contribution_title' => $isProduct ? ($row['product_title'] ?? 'Unknown Product') : ($row['contribution_title'] ?? 'Unknown Contribution'),
                    'contribution_description' => $isProduct ? ($row['product_description'] ?? '') : ($row['contribution_description'] ?? ''),
                    'contribution_amount' => $amount,
                    'payment_count' => 0,
                    'total_paid' => 0,
                    'total_quantity' => 0,
                    'first_payment_date' => $row['payment_date'],
                    'last_payment_date' => $row['payment_date'],
                    'payment_ids' => [],
                ];
            }

            $groups[$groupKey]['payment_count']++;
            $groups[$groupKey]['total_paid'] += (float) ($row['amount_paid'] ?? 0);
            $groups[$groupKey]['total_quantity'] += (int) ($row['quantity'] ?? 1);
            $groups[$groupKey]['payment_ids'][] = (int) $row['id'];

            if (strtotime((string) $row['payment_date']) < strtotime((string) $groups[$groupKey]['first_payment_date'])) {
                $groups[$groupKey]['first_payment_date'] = $row['payment_date'];
            }
            if (strtotime((string) $row['payment_date']) > strtotime((string) $groups[$groupKey]['last_payment_date'])) {
                $groups[$groupKey]['last_payment_date'] = $row['payment_date'];
            }
        }

        $refundModel = new RefundModel();
        foreach ($groups as &$group) {
            $totalRefunded = 0;

            foreach ($group['payment_ids'] as $paymentId) {
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
            if (($group['item_type'] ?? 'contribution') === 'product') {
                $group['remaining_balance'] = 0;
                $group['computed_status'] = 'fully paid';
            } else {
                $group['remaining_balance'] = max(0, (float) $group['contribution_amount'] - ((float) $group['total_paid'] - $totalRefunded));
                $group['computed_status'] = $group['remaining_balance'] <= 0 ? 'fully paid' : (((float) $group['total_paid']) > 0 ? 'partial' : 'unpaid');
            }
            $group['refund_status'] = ($totalRefunded >= (float)$group['total_paid']) ? 'fully_refunded' : 
                                      (($totalRefunded > 0) ? 'partially_refunded' : 'no_refund');
            unset($group['payment_ids']);
        }

        usort($groups, static function ($a, $b) {
            return strtotime((string) $b['last_payment_date']) <=> strtotime((string) $a['last_payment_date']);
        });

        return array_values($groups);
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
            COALESCE(contributions.title, products.title) as contribution_title,
            COALESCE(contributions.description, products.description) as contribution_description,
            COALESCE(contributions.amount, products.amount) as contribution_amount,
            contributions.contribution_code,
            CASE WHEN payments.product_id IS NOT NULL THEN \'product\' ELSE \'contribution\' END as item_type,
            users.username as recorded_by_name
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
        ->join('products', 'products.id = payments.product_id', 'left')
        ->join('users', 'users.id = payments.recorded_by', 'left')
        ->where('payments.payer_id', $payerId)
        ->where('payments.contribution_id', $contributionId)
        ->where('payments.deleted_at', null);
        
        $payments = $builder->orderBy('payments.payment_date', 'DESC')->findAll();

        if (empty($payments)) {
            return [];
        }

        return [[
            'sequence' => 1,
            'payments' => $payments,
            'total_amount' => array_sum(array_column($payments, 'amount_paid')),
            'payment_count' => count($payments),
            'first_payment_date' => min(array_column($payments, 'payment_date')),
            'last_payment_date' => max(array_column($payments, 'payment_date')),
        ]];
    }

    public function getPaymentsByPayerAndProduct($payerId, $productId, $paymentSequence = null)
    {
        $builder = $this->select('
            payments.*,
            payments.quantity,
            payers.payer_name,
            payers.payer_id as payer_student_id,
            payers.contact_number,
            payers.email_address,
            payers.profile_picture,
            products.title as contribution_title,
            products.description as contribution_description,
            products.amount as contribution_amount,
            \'product\' as item_type,
            users.username as recorded_by_name
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('products', 'products.id = payments.product_id', 'left')
        ->join('users', 'users.id = payments.recorded_by', 'left')
        ->where('payments.payer_id', $payerId)
        ->where('payments.product_id', $productId)
        ->where('payments.deleted_at', null);

        $payments = $builder->orderBy('payments.payment_date', 'DESC')->findAll();

        return array_map(static function ($payment) {
            return [
                'sequence' => $payment['id'],
                'payments' => [$payment],
                'total_amount' => $payment['amount_paid'],
                'payment_count' => 1,
                'first_payment_date' => $payment['payment_date'],
                'last_payment_date' => $payment['payment_date'],
            ];
        }, $payments);
    }
}
