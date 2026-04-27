<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class AdminWorkflowSmokeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';
    protected $seed = 'Tests\Support\Database\Seeds\WorkflowSmokeSeeder';
    protected $refresh = true;

    private function adminSession(): array
    {
        return [
            'user-id' => 1,
            'username' => 'adminsmoke',
            'email' => 'adminsmoke@example.com',
            'name' => 'Admin Smoke',
            'role' => 'admin',
            'isLoggedIn' => true,
        ];
    }

    public function testProtectedRouteRedirectsWhenUnauthenticated(): void
    {
        $result = $this->call('get', 'payments/search-payers', ['term' => 'lia']);

        $result->assertRedirectTo('/');
    }

    public function testAdminLoginRedirectsToDashboardWithValidCredentials(): void
    {
        $result = $this->call('post', 'loginPost', [
            'username' => 'adminsmoke',
            'password' => 'Secret123!',
        ]);

        $result->assertRedirectTo('/dashboard');
    }

    public function testAdminForgotPasswordResponseDoesNotExposeResetCode(): void
    {
        $result = $this->call('post', 'forgotPasswordPost', [
            'email' => 'adminsmoke@example.com',
        ]);

        $result->assertStatus(200);
        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($payload['success']);
        $this->assertArrayNotHasKey('reset_code', $payload);

        $admin = db_connect('tests')
            ->table('users')
            ->where('email', 'adminsmoke@example.com')
            ->get()
            ->getRowArray();

        $this->assertNotNull($admin);
        $this->assertNotEmpty($admin['reset_token']);
        $this->assertNotEmpty($admin['reset_expires']);
    }

    public function testAuthenticatedPayerSearchReturnsMatchingResults(): void
    {
        $result = $this
            ->withSession($this->adminSession())
            ->call('get', 'payments/search-payers', ['term' => 'lian']);

        $result->assertStatus(200);

        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($payload['success']);
        $this->assertCount(1, $payload['results']);
        $this->assertSame('Lianne Santos', $payload['results'][0]['payer_name']);
        $this->assertSame('2024-0001', $payload['results'][0]['payer_id']);
    }

    public function testContributionWarningDataReportsPartialAndFullyPaidStates(): void
    {
        $partial = $this
            ->withSession($this->adminSession())
            ->call('get', 'payments/get-contribution-warning-data', [
                'payer_id' => 1,
                'contribution_id' => 1,
            ]);

        $partial->assertStatus(200);
        $partialPayload = json_decode($partial->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($partialPayload['success']);
        $this->assertSame('unpaid', $partialPayload['status']);
        $this->assertSame(60.0, (float) $partialPayload['unpaid_group']['remaining_amount']);

        $full = $this
            ->withSession($this->adminSession())
            ->call('get', 'payments/get-contribution-warning-data', [
                'payer_id' => 1,
                'contribution_id' => 2,
            ]);

        $full->assertStatus(200);
        $fullPayload = json_decode($full->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($fullPayload['success']);
        $this->assertSame('fully_paid', $fullPayload['status']);
        $this->assertSame(100.0, (float) $fullPayload['fully_paid_groups'][0]['total_paid']);
    }

    public function testPaymentSaveCreatesANewPartialPayment(): void
    {
        $result = $this
            ->withSession($this->adminSession())
            ->call('post', 'payments/save', [
                'payer_id' => '2',
                'contribution_id' => '3',
                'amount_paid' => '50.00',
                'payment_method' => 'Cash',
                'is_partial_payment' => '1',
                'remaining_balance' => '70.00',
                'payment_date' => '2026-04-07 09:30:00',
            ]);

        $result->assertStatus(200);

        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($payload['success']);
        $this->assertSame('Payment recorded successfully.', $payload['message']);
        $this->assertSame('Marco Reyes', $payload['payment']['payer_name']);
        $this->assertSame('partial', $payload['payment']['payment_status']);
        $this->assertSame(50.0, (float) $payload['payment']['amount_paid']);
    }

    public function testAdminCanProcessProductRefundForApprovedProductPayment(): void
    {
        $db = db_connect('tests');
        $now = date('Y-m-d H:i:s');

        $db->table('products')->insert([
            'id' => 1,
            'title' => 'Uniform Shirt',
            'description' => 'Smoke product',
            'amount' => 500.00,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $db->table('payments')->insert([
            'id' => 10,
            'payer_id' => 1,
            'contribution_id' => null,
            'product_id' => 1,
            'quantity' => 2,
            'amount_paid' => 1000.00,
            'payment_method' => 'gcash',
            'payment_status' => 'fully paid',
            'is_partial_payment' => 0,
            'remaining_balance' => 0.00,
            'payment_sequence' => 10,
            'reference_number' => 'REF-PROD-10',
            'receipt_number' => 'RCPT-PROD-10',
            'recorded_by' => 1,
            'payment_date' => '2026-04-07 10:00:00',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $result = $this
            ->withSession($this->adminSession())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->call('post', 'admin/refunds/process', [
                'refund_type' => 'group',
                'payer_id' => '1',
                'contribution_id' => '',
                'product_id' => '1',
                'payment_sequence' => '10',
                'refund_amount' => '1000.00',
                'refund_method' => 'cash',
                'refund_reason' => 'Duplicate product payment',
            ]);

        $result->assertStatus(200);
        $payload = json_decode($result->getJSON(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($payload['success']);
        $this->assertSame(1, (int) ($payload['payment_count'] ?? 0));
        $this->assertSame(1000.0, (float) ($payload['total_refunded'] ?? 0));

        $refund = $db->table('refunds')
            ->where('payment_id', 10)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        $this->assertNotNull($refund);
        $this->assertSame(1, (int) $refund['product_id']);
        $this->assertNull($refund['contribution_id']);
        $this->assertSame('completed', $refund['status']);
    }
}
