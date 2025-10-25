<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'contribution_id',
        'payer_id',
        'payer_name',
        'contact_number',
        'email_address',
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
                    payers.id,
                    payers.payer_id,
                    payers.payer_name,
                    payers.amount_paid,
                    payers.payment_status,
                    payers.payment_date,
                    contributions.title AS contribution_title,
                    users.username AS recorded_by_name
                ')
                ->join('contributions', 'contributions.id = payers.contribution_id', 'left')
                ->join('users', 'users.id = payers.recorded_by', 'left')
                ->orderBy('payers.payment_date', 'DESC')
                ->limit($limit)
                ->findAll();
    }
}
