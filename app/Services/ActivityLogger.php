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
            'target_audience' => $announcement['target_audience'] ?? 'payers'
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log contribution activity
     */
    public function logContribution($action, $contribution, $oldData = null)
    {
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
            'target_audience' => 'payers'
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log payment activity
     */
    public function logPayment($action, $payment, $oldData = null)
    {
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
            'target_audience' => 'payers'
        ];

        return $this->activityLogModel->logActivity($data);
    }

    /**
     * Log payer activity
     */
    public function logPayer($action, $payer, $oldData = null)
    {
        $data = [
            'activity_type' => 'payer',
            'entity_type' => 'payer',
            'entity_id' => $payer['id'],
            'action' => $action,
            'title' => $this->getPayerTitle($action, $payer),
            'description' => $this->getPayerDescription($action, $payer, $oldData),
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => json_encode($payer),
            'user_id' => session('user-id') ?? 1,
            'user_type' => 'admin',
            'target_audience' => 'payers'
        ];

        return $this->activityLogModel->logActivity($data);
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
}
