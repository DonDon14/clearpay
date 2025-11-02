<?php

namespace App\Services;

use App\Models\ActivityLogModel;

class ActivityLogger
{
    protected $activityLogModel;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }

    /**
     * Log announcement activity
     */
    public function logAnnouncement($action, $announcement, $oldData = null)
    {
        $data = [
            'activity_type' => 'announcement',
            'entity_type' => 'announcement',
            'entity_id' => $announcement['id'],
            'action' => $action,
            'title' => $this->getAnnouncementTitle($action, $announcement),
            'description' => $this->getAnnouncementDescription($action, $announcement, $oldData),
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => json_encode($announcement),
            'user_id' => session('user-id') ?? 1,
            'user_type' => 'admin',
            'payer_id' => null, // General announcements don't target specific payers
            'target_audience' => $announcement['target_audience'] ?? 'payers',
            'is_read' => 0
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log contribution activity
     */
    public function logContribution($action, $contribution, $oldData = null)
    {
        // Contributions are admin-created, so always notify admins
        // Also notify payers so they know about new/updated contributions
        $targetAudience = 'both';
        
        $data = [
            'activity_type' => 'contribution',
            'entity_type' => 'contribution',
            'entity_id' => $contribution['id'],
            'action' => $action,
            'title' => $this->getContributionTitle($action, $contribution),
            'description' => $this->getContributionDescription($action, $contribution, $oldData),
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => json_encode($contribution),
            'user_id' => session('user-id') ?? 1,
            'user_type' => 'admin',
            'payer_id' => null, // General contributions don't target specific payers
            'target_audience' => $targetAudience,
            'is_read' => 0
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log payment activity
     */
    public function logPayment($action, $payment, $oldData = null)
    {
        // Determine target audience based on action
        // New payments should notify both admins and payers
        // Updates/deletes might be admin-only
        $targetAudience = ($action === 'created') ? 'both' : 'payers';
        
        $data = [
            'activity_type' => 'payment',
            'entity_type' => 'payment',
            'entity_id' => $payment['id'],
            'action' => $action,
            'title' => $this->getPaymentTitle($action, $payment),
            'description' => $this->getPaymentDescription($action, $payment, $oldData),
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => json_encode($payment),
            'user_id' => session('user-id') ?? 1,
            'user_type' => 'admin',
            'payer_id' => $payment['payer_id'] ?? null, // Specific payer for payment notifications
            'target_audience' => $targetAudience,
            'is_read' => 0
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log payer activity
     */
    public function logPayer($action, $payer, $oldData = null)
    {
        // Determine user type - if payer is updating their own profile, user_type should be 'payer'
        // Otherwise, it's an admin action
        $userType = session('payer_id') == ($payer['id'] ?? null) ? 'payer' : 'admin';
        $userId = session('payer_id') ?? session('user-id') ?? 1;
        
        // Determine target audience:
        // - New payer signup: notify admins
        // - Payer profile updates by payer: notify admins (so admins know about changes)
        // - Payer updates/deletes by admin: notify admins (admin activity)
        $targetAudience = 'admins'; // Always notify admins about payer activities
        
        $data = [
            'activity_type' => 'payer',
            'entity_type' => 'payer',
            'entity_id' => $payer['id'],
            'action' => $action,
            'title' => $this->getPayerTitle($action, $payer),
            'description' => $this->getPayerDescription($action, $payer, $oldData),
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => json_encode($payer),
            'user_id' => $userId,
            'user_type' => $userType,
            'payer_id' => $payer['id'] ?? null, // Specific payer for payer notifications
            'target_audience' => $targetAudience,
            'is_read' => 0
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log payment request activity
     */
    public function logPaymentRequest($action, $paymentRequest, $oldData = null)
    {
        // Determine user type based on session
        $userType = session('payer_id') ? 'payer' : 'admin';
        $userId = session('payer_id') ?? session('user-id') ?? 1;
        
        // Handle case where ID might not exist yet (for new requests)
        $entityId = $paymentRequest['id'] ?? null;
        
        // Determine target audience:
        // - New payment request created by payer: notify admins (they need to approve)
        // - Request approved/rejected by admin: notify both payers (they need to know the result) and admins (they need to see activity)
        $targetAudience = in_array($action, ['created', 'submitted']) ? 'admins' : 'both';
        
        $data = [
            'activity_type' => 'payment_request',
            'entity_type' => 'payment_request',
            'entity_id' => $entityId,
            'action' => $action,
            'title' => $this->getPaymentRequestTitle($action, $paymentRequest),
            'description' => $this->getPaymentRequestDescription($action, $paymentRequest, $oldData),
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => json_encode($paymentRequest),
            'user_id' => $userId,
            'user_type' => $userType,
            'payer_id' => $paymentRequest['payer_id'] ?? null,
            'target_audience' => $targetAudience,
            'is_read' => 0
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log refund activity
     */
    public function logRefund($action, $refund, $adminName = null)
    {
        $userId = session('user-id') ?? session('payer_id') ?? 1;
        $payerId = $refund['payer_id'] ?? null;
        
        // Determine user type - payer requested or admin processed
        $userType = session('payer_id') ? 'payer' : 'admin';
        
        // Get admin name if not provided and it's an admin action
        if (!$adminName && $userType === 'admin' && $userId) {
            $userModel = new \App\Models\UserModel();
            $admin = $userModel->find($userId);
            $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
        }
        
        // Determine target audience:
        // - Refund requested by payer: notify admins (they need to process)
        // - Refund processed/approved/rejected by admin: notify payers (they need to know the result)
        // - For consistency, also notify admins about all refund activities
        $targetAudience = in_array($action, ['requested']) ? 'admins' : 'both';
        
        $data = [
            'activity_type' => 'refund',
            'entity_type' => 'refund',
            'entity_id' => $refund['id'] ?? null,
            'action' => $action,
            'title' => $this->getRefundTitle($action, $refund, $adminName),
            'description' => $this->getRefundDescription($action, $refund, $adminName),
            'old_values' => null,
            'new_values' => json_encode($refund),
            'user_id' => $userId,
            'user_type' => $userType,
            'payer_id' => $payerId,
            'target_audience' => $targetAudience,
            'is_read' => 0
        ];

        // Log to activity_logs table
        $this->activityLogModel->logActivity($data);
        
        // Also log to user_activities table for dashboard display
        $this->logActivity(
            'refund_' . $action,
            'refund',
            $refund['id'] ?? null,
            $this->getRefundDescription($action, $refund, $adminName),
            $payerId
        );

        return true;
    }

    /**
     * Generic activity logging method
     */
    public function logActivity($action, $entityType, $entityId, $description, $payerId = null)
    {
        try {
            $db = \Config\Database::connect();
            
            $data = [
                'user_id' => session('user-id') ?? 1,
                'activity_type' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'metadata' => json_encode(['payer_id' => $payerId]),
                'ip_address' => service('request')->getIPAddress(),
                'user_agent' => service('request')->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            return $db->table('user_activities')->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
            return false;
        }
    }

    // Helper methods for generating titles and descriptions

    private function getAnnouncementTitle($action, $announcement)
    {
        switch ($action) {
            case 'created':
                return "New Announcement: {$announcement['title']}";
            case 'updated':
                return "Announcement Updated: {$announcement['title']}";
            case 'published':
                return "Announcement Published: {$announcement['title']}";
            case 'unpublished':
                return "Announcement Unpublished: {$announcement['title']}";
            default:
                return "Announcement {$action}: {$announcement['title']}";
        }
    }

    private function getAnnouncementDescription($action, $announcement, $oldData)
    {
        switch ($action) {
            case 'created':
                return "A new announcement has been created.";
            case 'updated':
                if ($oldData) {
                    $changes = [];
                    if (isset($oldData['title']) && $announcement['title'] !== $oldData['title']) {
                        $changes[] = "Title: {$oldData['title']} → {$announcement['title']}";
                    }
                    if (isset($oldData['type']) && $announcement['type'] !== $oldData['type']) {
                        $changes[] = "Type: {$oldData['type']} → {$announcement['type']}";
                    }
                    if (isset($oldData['priority']) && $announcement['priority'] !== $oldData['priority']) {
                        $changes[] = "Priority: {$oldData['priority']} → {$announcement['priority']}";
                    }
                    if (isset($oldData['status']) && $announcement['status'] !== $oldData['status']) {
                        $changes[] = "Status: {$oldData['status']} → {$announcement['status']}";
                    }
                    if (isset($oldData['target_audience']) && $announcement['target_audience'] !== $oldData['target_audience']) {
                        $changes[] = "Target: {$oldData['target_audience']} → {$announcement['target_audience']}";
                    }
                    
                    if (!empty($changes)) {
                        return "Announcement updated: " . implode(', ', $changes);
                    }
                }
                return "An announcement has been updated with new information.";
            case 'published':
                return "An announcement has been published and is now visible to payers.";
            case 'unpublished':
                return "An announcement has been unpublished and is no longer visible.";
            default:
                return "Announcement has been {$action}.";
        }
    }

    private function getContributionTitle($action, $contribution)
    {
        switch ($action) {
            case 'created':
                return "New Contribution: {$contribution['title']}";
            case 'updated':
                return "Contribution Updated: {$contribution['title']}";
            case 'deleted':
                return "Contribution Removed: {$contribution['title']}";
            default:
                return "Contribution {$action}: {$contribution['title']}";
        }
    }

    private function getContributionDescription($action, $contribution, $oldData)
    {
        switch ($action) {
            case 'created':
                return "A new contribution has been added to the system.";
            case 'updated':
                if ($oldData) {
                    $changes = [];
                    if (isset($oldData['title']) && $contribution['title'] !== $oldData['title']) {
                        $changes[] = "Title: {$oldData['title']} → {$contribution['title']}";
                    }
                    if (isset($oldData['amount']) && $contribution['amount'] != $oldData['amount']) {
                        $changes[] = "Amount: ₱" . number_format($oldData['amount'], 2) . " → ₱" . number_format($contribution['amount'], 2);
                    }
                    if (isset($oldData['status']) && $contribution['status'] !== $oldData['status']) {
                        $changes[] = "Status: {$oldData['status']} → {$contribution['status']}";
                    }
                    if (isset($oldData['category']) && $contribution['category'] !== $oldData['category']) {
                        $changes[] = "Category: {$oldData['category']} → {$contribution['category']}";
                    }
                    
                    if (!empty($changes)) {
                        return "Contribution updated: " . implode(', ', $changes);
                    }
                }
                return "Contribution details have been updated.";
            case 'deleted':
                return "A contribution has been removed from the system.";
            default:
                return "Contribution has been {$action}.";
        }
    }

    private function getPaymentTitle($action, $payment)
    {
        switch ($action) {
            case 'created':
                return "New Payment Recorded: ₱" . number_format($payment['amount_paid'], 2);
            case 'updated':
                return "Payment Updated: ₱" . number_format($payment['amount_paid'], 2);
            case 'deleted':
                return "Payment Removed: ₱" . number_format($payment['amount_paid'], 2);
            default:
                return "Payment {$action}: ₱" . number_format($payment['amount_paid'], 2);
        }
    }

    private function getPaymentDescription($action, $payment, $oldData)
    {
        switch ($action) {
            case 'created':
                return "A new payment has been recorded in the system.";
            case 'updated':
                if ($oldData) {
                    $changes = [];
                    if (isset($oldData['amount_paid']) && $payment['amount_paid'] != $oldData['amount_paid']) {
                        $changes[] = "Amount: ₱" . number_format($oldData['amount_paid'], 2) . " → ₱" . number_format($payment['amount_paid'], 2);
                    }
                    if (isset($oldData['payment_method']) && $payment['payment_method'] !== $oldData['payment_method']) {
                        $changes[] = "Method: {$oldData['payment_method']} → {$payment['payment_method']}";
                    }
                    if (isset($oldData['payment_status']) && $payment['payment_status'] !== $oldData['payment_status']) {
                        $changes[] = "Status: {$oldData['payment_status']} → {$payment['payment_status']}";
                    }
                    
                    if (!empty($changes)) {
                        return "Payment updated: " . implode(', ', $changes);
                    }
                }
                return "Payment details have been updated.";
            case 'deleted':
                return "A payment record has been removed.";
            default:
                return "Payment has been {$action}.";
        }
    }

    private function getPayerTitle($action, $payer)
    {
        switch ($action) {
            case 'created':
                return "New Payer Added: {$payer['payer_name']}";
            case 'updated':
                return "Payer Updated: {$payer['payer_name']}";
            case 'deleted':
                return "Payer Removed: {$payer['payer_name']}";
            default:
                return "Payer {$action}: {$payer['payer_name']}";
        }
    }

    private function getPayerDescription($action, $payer, $oldData)
    {
        switch ($action) {
            case 'created':
                return "A new payer has been added to the system.";
            case 'updated':
                if ($oldData) {
                    $changes = [];
                    if (isset($oldData['payer_name']) && $payer['payer_name'] !== $oldData['payer_name']) {
                        $changes[] = "Name: {$oldData['payer_name']} → {$payer['payer_name']}";
                    }
                    if (isset($oldData['email_address']) && $payer['email_address'] !== $oldData['email_address']) {
                        $changes[] = "Email: {$oldData['email_address']} → {$payer['email_address']}";
                    }
                    if (isset($oldData['contact_number']) && $payer['contact_number'] !== $oldData['contact_number']) {
                        $changes[] = "Phone: {$oldData['contact_number']} → {$payer['contact_number']}";
                    }
                    
                    if (!empty($changes)) {
                        return "Payer updated: " . implode(', ', $changes);
                    }
                }
                return "Payer information has been updated.";
            case 'deleted':
                return "A payer has been removed from the system.";
            default:
                return "Payer has been {$action}.";
        }
    }

    private function getPaymentRequestTitle($action, $paymentRequest)
    {
        switch ($action) {
            case 'created':
            case 'submitted':
                return "Payment Request Submitted";
            case 'approved':
                return "Payment Request Approved";
            case 'rejected':
                return "Payment Request Rejected";
            case 'processed':
                return "Payment Request Processed";
            default:
                return "Payment Request {$action}";
        }
    }

    private function getPaymentRequestDescription($action, $paymentRequest, $oldData)
    {
        $amount = number_format($paymentRequest['requested_amount'], 2);
        
        switch ($action) {
            case 'created':
            case 'submitted':
                return "Submitted payment request for ₱{$amount}";
            case 'approved':
                return "Payment request for ₱{$amount} has been approved and payment recorded.";
            case 'rejected':
                $reason = $paymentRequest['admin_notes'] ? " Reason: {$paymentRequest['admin_notes']}" : "";
                return "Payment request for ₱{$amount} has been rejected.{$reason}";
            case 'processed':
                return "Payment request for ₱{$amount} has been processed and payment recorded.";
            default:
                return "Payment request for ₱{$amount} has been {$action}.";
        }
    }

    private function getRefundTitle($action, $refund, $adminName = null)
    {
        $amount = number_format($refund['refund_amount'], 2);
        
        switch ($action) {
            case 'requested':
                return "Refund Requested: ₱{$amount}";
            case 'processed':
                return "Refund Processed: ₱{$amount}";
            case 'approved':
                return "Refund Approved: ₱{$amount}";
            case 'rejected':
                return "Refund Rejected: ₱{$amount}";
            case 'completed':
                return "Refund Completed: ₱{$amount}";
            default:
                return "Refund {$action}: ₱{$amount}";
        }
    }

    private function getRefundDescription($action, $refund, $adminName = null)
    {
        $amount = number_format($refund['refund_amount'], 2);
        
        switch ($action) {
            case 'requested':
                $payerName = $refund['payer_name'] ?? 'A payer';
                return "{$payerName} has requested a refund of ₱{$amount}.";
            case 'processed':
                return "Refund of ₱{$amount} has been processed by {$adminName}.";
            case 'approved':
                return "Refund request for ₱{$amount} has been approved by {$adminName}.";
            case 'rejected':
                $reason = $refund['admin_notes'] ? " Reason: {$refund['admin_notes']}" : "";
                return "Refund request for ₱{$amount} has been rejected by {$adminName}.{$reason}";
            case 'completed':
                return "Refund of ₱{$amount} has been completed by {$adminName}.";
            default:
                $adminText = $adminName ? " by {$adminName}" : "";
                return "Refund of ₱{$amount} has been {$action}{$adminText}.";
        }
    }

    /**
     * Log user (admin) activity - for notifying other admins
     */
    public function logUser($action, $user, $oldData = null)
    {
        $data = [
            'activity_type' => 'user',
            'entity_type' => 'user',
            'entity_id' => $user['id'],
            'action' => $action,
            'title' => $this->getUserTitle($action, $user),
            'description' => $this->getUserDescription($action, $user, $oldData),
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => json_encode($user),
            'user_id' => session('user-id') ?? 1,
            'user_type' => 'admin',
            'payer_id' => null, // Admin activities don't target payers
            'target_audience' => 'admins', // Only notify admins about admin activities
            'is_read' => 0
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Get user activity title
     */
    private function getUserTitle($action, $user)
    {
        $userName = $user['name'] ?? $user['username'] ?? 'User';
        
        switch ($action) {
            case 'created':
                return "New Admin User Added: {$userName}";
            case 'updated':
                return "Admin User Updated: {$userName}";
            case 'deleted':
                return "Admin User Removed: {$userName}";
            default:
                return "Admin User {$action}: {$userName}";
        }
    }

    /**
     * Get user activity description
     */
    private function getUserDescription($action, $user, $oldData)
    {
        $userName = $user['name'] ?? $user['username'] ?? 'User';
        
        switch ($action) {
            case 'created':
                return "A new admin user '{$userName}' has been added to the system.";
            case 'updated':
                if ($oldData) {
                    $changes = [];
                    if (isset($oldData['name']) && $user['name'] !== $oldData['name']) {
                        $changes[] = "Name: {$oldData['name']} → {$user['name']}";
                    }
                    if (isset($oldData['username']) && $user['username'] !== $oldData['username']) {
                        $changes[] = "Username: {$oldData['username']} → {$user['username']}";
                    }
                    if (isset($oldData['role']) && $user['role'] !== $oldData['role']) {
                        $changes[] = "Role: {$oldData['role']} → {$user['role']}";
                    }
                    if (isset($oldData['email']) && $user['email'] !== $oldData['email']) {
                        $changes[] = "Email: {$oldData['email']} → {$user['email']}";
                    }
                    
                    if (!empty($changes)) {
                        return "Admin user '{$userName}' updated: " . implode(', ', $changes);
                    }
                }
                return "Admin user '{$userName}' has been updated.";
            case 'deleted':
                return "Admin user '{$userName}' has been removed from the system.";
            default:
                return "Admin user '{$userName}' has been {$action}.";
        }
    }
}
