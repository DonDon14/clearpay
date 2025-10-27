<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentRequestModel extends Model
{
    protected $table = 'payment_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'payer_id',
        'contribution_id',
        'payment_sequence',
        'requested_amount',
        'payment_method',
        'reference_number',
        'proof_of_payment_path',
        'status',
        'notes',
        'requested_at',
        'processed_at',
        'processed_by',
        'admin_notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'payer_id' => 'required|integer',
        'contribution_id' => 'required|integer',
        'requested_amount' => 'required|decimal',
        'payment_method' => 'required|in_list[cash,online,bank_transfer,gcash,paymaya]',
        'status' => 'required|in_list[pending,approved,rejected,processed]'
    ];

    protected $validationMessages = [
        'payer_id' => [
            'required' => 'Payer ID is required',
            'integer' => 'Payer ID must be a valid integer'
        ],
        'contribution_id' => [
            'required' => 'Contribution ID is required',
            'integer' => 'Contribution ID must be a valid integer'
        ],
        'requested_amount' => [
            'required' => 'Requested amount is required',
            'decimal' => 'Requested amount must be a valid decimal'
        ],
        'payment_method' => [
            'required' => 'Payment method is required',
            'in_list' => 'Payment method must be one of: cash, online, bank_transfer, gcash, paymaya'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be one of: pending, approved, rejected, processed'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['beforeInsert'];
    protected $beforeUpdate = ['beforeUpdate'];

    protected function beforeInsert(array $data)
    {
        // Generate reference number if not provided
        if (empty($data['data']['reference_number'])) {
            $data['data']['reference_number'] = 'REQ-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 12));
        }
        
        // Set requested_at timestamp
        $data['data']['requested_at'] = date('Y-m-d H:i:s');
        
        return $data;
    }

    protected function beforeUpdate(array $data)
    {
        // Set processed_at timestamp when status changes to processed
        if (isset($data['data']['status']) && $data['data']['status'] === 'processed') {
            $data['data']['processed_at'] = date('Y-m-d H:i:s');
        }
        
        return $data;
    }

    /**
     * Get payment requests with payer and contribution details
     */
    public function getRequestsWithDetails($status = null, $limit = null)
    {
        $builder = $this->select('
            payment_requests.*,
            payers.payer_name,
            payers.contact_number,
            payers.email_address,
            payers.profile_picture,
            contributions.title as contribution_title,
            contributions.description as contribution_description,
            contributions.amount as contribution_amount,
            users.username as processed_by_name
        ')
        ->join('payers', 'payers.id = payment_requests.payer_id', 'left')
        ->join('contributions', 'contributions.id = payment_requests.contribution_id', 'left')
        ->join('users', 'users.id = payment_requests.processed_by', 'left');

        if ($status) {
            $builder->where('payment_requests.status', $status);
        }

        $builder->orderBy('payment_requests.requested_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Get pending payment requests count
     */
    public function getPendingCount()
    {
        return $this->where('status', 'pending')->countAllResults();
    }

    /**
     * Get payment requests by payer
     */
    public function getRequestsByPayer($payerId, $status = null)
    {
        $builder = $this->select('
            payment_requests.*,
            contributions.title as contribution_title,
            contributions.description as contribution_description,
            contributions.amount as contribution_amount
        ')
        ->join('contributions', 'contributions.id = payment_requests.contribution_id', 'left')
        ->where('payment_requests.payer_id', $payerId);
        
        if ($status) {
            $builder->where('payment_requests.status', $status);
        }
        
        return $builder->orderBy('payment_requests.requested_at', 'DESC')->findAll();
    }

    /**
     * Approve a payment request
     */
    public function approveRequest($requestId, $processedBy, $adminNotes = null)
    {
        return $this->update($requestId, [
            'status' => 'approved',
            'processed_by' => $processedBy,
            'admin_notes' => $adminNotes,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reject a payment request
     */
    public function rejectRequest($requestId, $processedBy, $adminNotes = null)
    {
        return $this->update($requestId, [
            'status' => 'rejected',
            'processed_by' => $processedBy,
            'admin_notes' => $adminNotes,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Process a payment request (convert to actual payment)
     */
    public function processRequest($requestId, $processedBy, $adminNotes = null)
    {
        return $this->update($requestId, [
            'status' => 'processed',
            'processed_by' => $processedBy,
            'admin_notes' => $adminNotes,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }
}
