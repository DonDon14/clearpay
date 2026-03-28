<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentRequestModel;
use App\Models\PayerModel;
use App\Models\RefundModel;
use App\Services\PythonAnalyticsService;

class ReviewCenterController extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $paymentRequestModel = new PaymentRequestModel();
        $refundModel = new RefundModel();
        $payerModel = new PayerModel();
        $db = \Config\Database::connect();

        $paymentRequests = $paymentRequestModel->getRequestsWithDetails('pending', 8);
        $refundRequests = $refundModel->getPendingRequests();
        $refundRequests = array_slice($refundRequests, 0, 8);

        $payerIssues = $payerModel
            ->select('id, payer_id, payer_name, email_address, contact_number, created_at')
            ->groupStart()
                ->where('password', null)
                ->orWhere('password', '')
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll(8);

        $emailHealth = $this->getEmailHealth($db);
        $analyticsAlerts = $this->getAnalyticsAlerts();
        $paymentRequests = $this->decoratePaymentRequests($paymentRequests);
        $refundRequests = $this->decorateRefundRequests($refundRequests);
        $payerIssues = $this->decoratePayerIssues($payerIssues);

        $summary = [
            'pending_payment_requests' => $paymentRequestModel->getPendingCount(),
            'pending_refunds' => $refundModel->getPendingCount(),
            'duplicate_alerts' => count($analyticsAlerts['duplicates']),
            'suspicious_alerts' => count($analyticsAlerts['suspicious']),
            'payer_account_issues' => $payerModel
                ->groupStart()
                    ->where('password', null)
                    ->orWhere('password', '')
                ->groupEnd()
                ->countAllResults(),
            'email_issues' => $emailHealth['issue_count'],
        ];

        $data = [
            'title' => 'Review Center',
            'pageTitle' => 'Review Center',
            'pageSubtitle' => 'Priority queues and operational issues that need admin attention',
            'summary' => $summary,
            'paymentRequests' => $paymentRequests,
            'refundRequests' => $refundRequests,
            'analyticsAlerts' => $analyticsAlerts,
            'payerIssues' => $payerIssues,
            'emailHealth' => $emailHealth,
        ];

        return view('admin/review-center', $data);
    }

    public function pendingCount()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized',
            ])->setStatusCode(401);
        }

        $paymentRequestModel = new PaymentRequestModel();
        $refundModel = new RefundModel();
        // Sidebar badges should reflect queue-like items that need immediate action,
        // not persistent diagnostics such as analytics findings or config warnings.
        $count = (int) $paymentRequestModel->getPendingCount()
            + (int) $refundModel->getPendingCount();

        return $this->response->setJSON([
            'success' => true,
            'count' => $count,
            'breakdown' => [
                'pending_payment_requests' => (int) $paymentRequestModel->getPendingCount(),
                'pending_refunds' => (int) $refundModel->getPendingCount(),
            ],
        ]);
    }

    private function getAnalyticsAlerts(): array
    {
        try {
            $analytics = (new PythonAnalyticsService())->generateAnalytics();
            $payments = $analytics['payments'] ?? [];

            return [
                'duplicates' => array_slice($payments['duplicates'] ?? [], 0, 8),
                'suspicious' => array_slice($payments['suspicious'] ?? [], 0, 8),
                'generated_at' => $analytics['generated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            log_message('warning', 'Review Center analytics unavailable: ' . $e->getMessage());

            return [
                'duplicates' => [],
                'suspicious' => [],
                'generated_at' => null,
                'error' => 'Python analytics data is currently unavailable.',
            ];
        }
    }

    private function getEmailHealth($db): array
    {
        $issues = [];
        $settings = null;

        if (!$db->tableExists('email_settings')) {
            $issues[] = 'Email settings table is missing.';
        } else {
            $settings = $db->table('email_settings')
                ->where('is_active', true)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$settings) {
                $issues[] = 'No active email configuration found.';
            } else {
                $requiredMap = [
                    'from_email' => 'Sender email',
                    'smtp_host' => 'SMTP host',
                    'smtp_user' => 'SMTP username',
                    'smtp_pass' => 'SMTP password',
                    'smtp_port' => 'SMTP port',
                ];

                foreach ($requiredMap as $field => $label) {
                    if (empty($settings[$field])) {
                        $issues[] = $label . ' is missing.';
                    }
                }

                if (($settings['smtp_host'] ?? '') === 'smtp-relay.brevo.com'
                    && str_starts_with((string) ($settings['smtp_pass'] ?? ''), 'xsmtpsib-')
                    && empty(getenv('BREVO_API_KEY'))
                ) {
                    $issues[] = 'Brevo SMTP is configured without a Brevo API fallback key.';
                }
            }
        }

        return [
            'settings' => $settings,
            'issues' => $issues,
            'issue_count' => count($issues),
            'status' => empty($issues) ? 'healthy' : 'warning',
        ];
    }

    private function decoratePaymentRequests(array $requests): array
    {
        foreach ($requests as &$request) {
            $ageHours = $this->getAgeHours($request['requested_at'] ?? null);
            $request['priority_score'] = 50 + min($ageHours, 72);
            $request['severity_label'] = $ageHours >= 24 ? 'High' : ($ageHours >= 8 ? 'Medium' : 'Normal');
        }

        usort($requests, static fn ($a, $b) => ($b['priority_score'] ?? 0) <=> ($a['priority_score'] ?? 0));
        return $requests;
    }

    private function decorateRefundRequests(array $requests): array
    {
        foreach ($requests as &$request) {
            $ageHours = $this->getAgeHours($request['requested_at'] ?? null);
            $request['priority_score'] = 60 + min($ageHours, 72);
            $request['severity_label'] = $ageHours >= 24 ? 'High' : ($ageHours >= 8 ? 'Medium' : 'Normal');
        }

        usort($requests, static fn ($a, $b) => ($b['priority_score'] ?? 0) <=> ($a['priority_score'] ?? 0));
        return $requests;
    }

    private function decoratePayerIssues(array $issues): array
    {
        foreach ($issues as &$issue) {
            $ageHours = $this->getAgeHours($issue['created_at'] ?? null);
            $issue['severity_label'] = $ageHours >= 168 ? 'High' : 'Medium';
        }

        return $issues;
    }

    private function getAgeHours(?string $timestamp): int
    {
        if (empty($timestamp)) {
            return 0;
        }

        $createdAt = strtotime($timestamp);
        if ($createdAt === false) {
            return 0;
        }

        return max(0, (int) floor((time() - $createdAt) / 3600));
    }
}
