<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentMethodModel extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'icon',
        'description',
        'account_details',
        'account_number',
        'account_name',
        'qr_code_path',
        'custom_instructions',
        'reference_prefix',
        'status',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'icon' => 'permit_empty|max_length[255]',
        'description' => 'permit_empty|max_length[1000]',
        'account_details' => 'permit_empty|max_length[255]',
        'account_number' => 'permit_empty|max_length[100]',
        'account_name' => 'permit_empty|max_length[100]',
        'qr_code_path' => 'permit_empty|max_length[255]',
        'custom_instructions' => 'permit_empty',
        'reference_prefix' => 'permit_empty|max_length[20]',
        'status' => 'required|in_list[active,inactive]',
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Payment method name is required.',
            'max_length' => 'Payment method name cannot exceed 100 characters.',
        ],
        'icon' => [
            'max_length' => 'Icon file path cannot exceed 255 characters.',
        ],
        'description' => [
            'max_length' => 'Description cannot exceed 1000 characters.',
        ],
        'account_details' => [
            'max_length' => 'Account details cannot exceed 255 characters.',
        ],
        'account_number' => [
            'max_length' => 'Account number cannot exceed 100 characters.',
        ],
        'account_name' => [
            'max_length' => 'Account name cannot exceed 100 characters.',
        ],
        'qr_code_path' => [
            'max_length' => 'QR code file path cannot exceed 255 characters.',
        ],
        'reference_prefix' => [
            'max_length' => 'Reference prefix cannot exceed 20 characters.',
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be either active or inactive.',
        ],
    ];

    /**
     * Get all active payment methods
     */
    public function getActiveMethods()
    {
        return $this->where('status', 'active')->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get payment method by ID
     */
    public function getMethodById($id)
    {
        return $this->find($id);
    }

    /**
     * Toggle payment method status
     */
    public function toggleStatus($id)
    {
        $method = $this->find($id);
        if (!$method) {
            return false;
        }

        $newStatus = $method['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['status' => $newStatus]);
    }

    /**
     * Get payment method by name (for dynamic instructions)
     */
    public function getMethodByName($name)
    {
        return $this->where('name', $name)->where('status', 'active')->first();
    }

    /**
     * Get payment method with custom instructions by name
     */
    public function getMethodWithInstructions($name)
    {
        $method = $this->getMethodByName($name);
        if ($method && $method['custom_instructions']) {
            // Replace placeholders in custom instructions
            $method['processed_instructions'] = $this->processCustomInstructions($method['custom_instructions'], $method);
        }
        return $method;
    }

    /**
     * Process custom instructions with dynamic data
     */
    private function processCustomInstructions($instructions, $method)
    {
        $placeholders = [
            '{account_number}' => $method['account_number'] ?? '',
            '{account_name}' => $method['account_name'] ?? '',
            '{qr_code_path}' => $method['qr_code_path'] ? base_url($method['qr_code_path']) : '',
            '{reference_prefix}' => $method['reference_prefix'] ?? 'CP',
            '{method_name}' => $method['name'] ?? '',
            '{amount}' => '{amount}', // This will be replaced by JavaScript with actual amount
            '{timestamp}' => time() // Current timestamp for unique references
        ];

        // Log for debugging
        log_message('debug', 'Processing custom instructions for method: ' . $method['name']);
        log_message('debug', 'QR code path: ' . ($method['qr_code_path'] ?? 'none'));
        log_message('debug', 'QR code URL: ' . ($method['qr_code_path'] ? base_url($method['qr_code_path']) : 'none'));

        return str_replace(array_keys($placeholders), array_values($placeholders), $instructions);
    }
}
