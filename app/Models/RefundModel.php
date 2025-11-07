<?php

namespace App\Models;

use CodeIgniter\Model;

class RefundModel extends Model
{
    protected $table = 'refunds';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'payment_id',
        'payer_id',
        'contribution_id',
        'refund_amount',
        'refund_reason',
        'refund_method',
        'refund_reference',
        'status',
        'request_type',
        'requested_by_payer',
        'requested_at',
        'processed_at',
        'processed_by',
        'admin_notes',
        'payer_notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'payment_id' => 'required|integer',
        'payer_id' => 'required|integer',
        'contribution_id' => 'required|integer',
        'refund_amount' => 'required|decimal',
        'refund_method' => 'required|alpha_dash|max_length[50]', // Dynamic validation happens in controller
        'status' => 'required|in_list[pending,processing,completed,rejected,cancelled]',
        'request_type' => 'required|in_list[admin_initiated,payer_requested]'
    ];
    
    /**
     * Validate refund method against database
     * This should be called from the controller before inserting/updating
     */
    public function validateRefundMethod($method)
    {
        $refundMethodModel = new \App\Models\RefundMethodModel();
        $validCodes = $refundMethodModel->getAllCodes(); // Get all codes (active + inactive)
        
        // Also allow 'original_method' as a special case for backward compatibility
        $validCodes[] = 'original_method';
        
        return in_array($method, $validCodes);
    }

    protected $validationMessages = [
        'payment_id' => [
            'required' => 'Payment ID is required',
            'integer' => 'Payment ID must be a valid integer'
        ],
        'payer_id' => [
            'required' => 'Payer ID is required',
            'integer' => 'Payer ID must be a valid integer'
        ],
        'refund_amount' => [
            'required' => 'Refund amount is required',
            'decimal' => 'Refund amount must be a valid decimal'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be one of: pending, processing, completed, rejected, cancelled'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['beforeInsert'];
    protected $beforeUpdate = ['beforeUpdate'];

    protected function beforeInsert(array $data)
    {
        // Generate refund reference number if not provided
        if (empty($data['data']['refund_reference'])) {
            $data['data']['refund_reference'] = 'REF-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 12));
        }
        
        // Set requested_at timestamp if not provided
        if (empty($data['data']['requested_at'])) {
            $data['data']['requested_at'] = date('Y-m-d H:i:s');
        }
        
        return $data;
    }

    protected function beforeUpdate(array $data)
    {
        // Set processed_at timestamp when status changes to completed or processing
        if (isset($data['data']['status']) && in_array($data['data']['status'], ['completed', 'processing'])) {
            if (empty($data['data']['processed_at'])) {
                $data['data']['processed_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $data;
    }

    /**
     * Get refunds with payment, payer, and contribution details
     */
    public function getRefundsWithDetails($status = null, $requestType = null, $limit = null)
    {
        $builder = $this->select('
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
            contributions.description as contribution_description,
            users.username as processed_by_username,
            users.name as processed_by_name
        ')
        ->join('payments', 'payments.id = refunds.payment_id', 'left')
        ->join('payers', 'payers.id = refunds.payer_id', 'left')
        ->join('contributions', 'contributions.id = refunds.contribution_id', 'left')
        ->join('users', 'users.id = refunds.processed_by', 'left');

        if ($status) {
            $builder->where('refunds.status', $status);
        }

        if ($requestType) {
            $builder->where('refunds.request_type', $requestType);
        }

        $builder->orderBy('refunds.requested_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Get pending refund requests (from payers)
     */
    public function getPendingRequests()
    {
        return $this->getRefundsWithDetails('pending', 'payer_requested');
    }

    /**
     * Get refund history
     */
    public function getRefundHistory($status = null, $limit = null)
    {
        $builder = $this->select('
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
            users.username as processed_by_username,
            users.name as processed_by_name
        ')
        ->join('payments', 'payments.id = refunds.payment_id', 'left')
        ->join('payers', 'payers.id = refunds.payer_id', 'left')
        ->join('contributions', 'contributions.id = refunds.contribution_id', 'left')
        ->join('users', 'users.id = refunds.processed_by', 'left')
        ->whereIn('refunds.status', ['completed', 'rejected', 'cancelled']);

        if ($status) {
            $builder->where('refunds.status', $status);
        }

        $builder->orderBy('refunds.processed_at', 'DESC')
                ->orderBy('refunds.requested_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Get refund requests by payer
     */
    public function getRequestsByPayer($payerId, $status = null)
    {
        $builder = $this->select('
            refunds.*,
            payments.amount_paid,
            payments.receipt_number,
            payments.payment_date,
            contributions.title as contribution_title
        ')
        ->join('payments', 'payments.id = refunds.payment_id', 'left')
        ->join('contributions', 'contributions.id = refunds.contribution_id', 'left')
        ->where('refunds.payer_id', $payerId);
        
        if ($status) {
            $builder->where('refunds.status', $status);
        }
        
        return $builder->orderBy('refunds.requested_at', 'DESC')->findAll();
    }

    /**
     * Approve and process a refund request
     */
    public function approveRequest($refundId, $processedBy, $adminNotes = null)
    {
        return $this->update($refundId, [
            'status' => 'processing',
            'processed_by' => $processedBy,
            'admin_notes' => $adminNotes,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Complete a refund
     */
    public function completeRefund($refundId, $processedBy, $adminNotes = null, $refundReference = null)
    {
        $data = [
            'status' => 'completed',
            'processed_by' => $processedBy,
            'processed_at' => date('Y-m-d H:i:s')
        ];

        if ($adminNotes) {
            $data['admin_notes'] = $adminNotes;
        }

        if ($refundReference) {
            $data['refund_reference'] = $refundReference;
        }

        return $this->update($refundId, $data);
    }

    /**
     * Reject a refund request
     */
    public function rejectRequest($refundId, $processedBy, $adminNotes = null)
    {
        return $this->update($refundId, [
            'status' => 'rejected',
            'processed_by' => $processedBy,
            'admin_notes' => $adminNotes,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Cancel a refund
     */
    public function cancelRefund($refundId, $processedBy, $adminNotes = null)
    {
        return $this->update($refundId, [
            'status' => 'cancelled',
            'processed_by' => $processedBy,
            'admin_notes' => $adminNotes,
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get refund statistics
     */
    public function getStats()
    {
        return [
            'pending' => $this->where('status', 'pending')->countAllResults(),
            'processing' => $this->where('status', 'processing')->countAllResults(),
            'completed' => $this->where('status', 'completed')->countAllResults(),
            'rejected' => $this->where('status', 'rejected')->countAllResults(),
            'total' => $this->countAllResults()
        ];
    }

    /**
     * Get pending refund requests count (from payers)
     */
    public function getPendingCount()
    {
        return $this->where('status', 'pending')
                    ->where('request_type', 'payer_requested')
                    ->countAllResults();
    }
}
