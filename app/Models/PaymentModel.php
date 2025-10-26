<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
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
        'updated_at'
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
}
