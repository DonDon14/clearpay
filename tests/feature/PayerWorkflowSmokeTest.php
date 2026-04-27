<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class PayerWorkflowSmokeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';
    protected $seed = 'Tests\Support\Database\Seeds\WorkflowSmokeSeeder';
    protected $refresh = true;

    private function payerSession(): array
    {
        return [
            'payer_id' => 1,
            'payer_student_id' => '2024-0001',
            'payer_name' => 'Lianne Santos',
            'payer_email' => 'lianne@example.com',
            'payer_logged_in' => true,
            'payer_last_activity' => time(),
        ];
    }

    public function testPayerLoginRedirectsToDashboardWithValidCredentials(): void
    {
        $result = $this->call('post', 'payer/loginPost', [
            'payer_id' => '2024-0001',
            'password' => 'payer-one',
        ]);

        $result->assertRedirectTo('/payer/dashboard');
    }

    public function testPayerPasswordResetFlowIssuesCodeVerifiesAndResetsPassword(): void
    {
        $sendReset = $this->call('post', 'payer/sendResetCode', [
            'email' => 'lianne@example.com',
        ]);

        $sendReset->assertStatus(200);
        $sendPayload = json_decode($sendReset->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($sendPayload['success']);
        $this->assertArrayNotHasKey('reset_code', $sendPayload);

        $payer = db_connect('tests')
            ->table('payers')
            ->where('email_address', 'lianne@example.com')
            ->get()
            ->getRowArray();
        $this->assertNotNull($payer);
        $this->assertNotEmpty($payer['reset_token']);

        $verify = $this->call('post', 'payer/verifyResetCode', [
            'email' => 'lianne@example.com',
            'reset_code' => (string) $payer['reset_token'],
        ]);

        $verify->assertStatus(200);
        $verifyPayload = json_decode($verify->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($verifyPayload['success']);

        $reset = $this
            ->withSession([
                'reset_verified_payer_id' => 1,
                'reset_verified_email' => 'lianne@example.com',
            ])
            ->call('post', 'payer/resetPassword', [
                'password' => 'payer-one-updated',
                'confirm_password' => 'payer-one-updated',
            ]);

        $reset->assertStatus(200);
        $resetPayload = json_decode($reset->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($resetPayload['success']);

        $login = $this->call('post', 'payer/loginPost', [
            'payer_id' => '2024-0001',
            'password' => 'payer-one-updated',
        ]);

        $login->assertRedirectTo('/payer/dashboard');
    }

    public function testPayerPaymentRequestCreatesPendingRecordAndActivityLog(): void
    {
        $result = $this
            ->withSession($this->payerSession())
            ->call('post', 'payer/submit-payment-request', [
                'contribution_id' => '3',
                'requested_amount' => '30.00',
                'payment_method' => 'Cash',
                'notes' => 'Smoke test payment request',
            ]);

        $result->assertStatus(200);
        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($payload['success']);
        $this->assertNotEmpty($payload['request_id']);

        $request = db_connect('tests')
            ->table('payment_requests')
            ->where('id', $payload['request_id'])
            ->get()
            ->getRowArray();

        $this->assertNotNull($request);
        $this->assertSame('pending', $request['status']);
        $this->assertSame('Cash', $request['payment_method']);
        $this->assertSame(30.0, (float) $request['requested_amount']);

        $activity = db_connect('tests')
            ->table('activity_logs')
            ->where('entity_type', 'payment_request')
            ->where('entity_id', $payload['request_id'])
            ->where('action', 'submitted')
            ->get()
            ->getRowArray();

        $this->assertNotNull($activity);
        $this->assertSame('admins', $activity['target_audience']);
        $this->assertSame('payer', $activity['user_type']);
    }

    public function testPayerRefundRequestCreatesPendingRecordAndActivityLog(): void
    {
        $result = $this
            ->withSession($this->payerSession())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->call('post', 'payer/submit-refund-request', [
                'payment_id' => '1',
                'refund_amount' => '20.00',
                'refund_method' => 'cash',
                'refund_reason' => 'Smoke test refund request',
            ]);

        $result->assertStatus(200);
        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($payload['success']);
        $this->assertNotEmpty($payload['refund_id']);

        $refund = db_connect('tests')
            ->table('refunds')
            ->where('id', $payload['refund_id'])
            ->get()
            ->getRowArray();

        $this->assertNotNull($refund);
        $this->assertSame('pending', $refund['status']);
        $this->assertSame('payer_requested', $refund['request_type']);
        $this->assertSame('cash', $refund['refund_method']);
        $this->assertSame(20.0, (float) $refund['refund_amount']);

        $activity = db_connect('tests')
            ->table('activity_logs')
            ->where('entity_type', 'refund')
            ->where('entity_id', $payload['refund_id'])
            ->where('action', 'requested')
            ->get()
            ->getRowArray();

        $this->assertNotNull($activity);
        $this->assertSame('admins', $activity['target_audience']);
        $this->assertSame('payer', $activity['user_type']);
    }
}
