<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\AccountVerificationRepository;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;

class AccountVerificationRepositoryTest extends TestCase
{

    protected AccountVerificationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AccountVerificationRepository();
    }

    /**
     * Test creating account verification record.
     */
    public function test_create_account_verification(): void
    {
        $data = [
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => ['test' => 'data'],
            'response_data' => ['test' => 'response'],
            'response_code' => '00',
            'account_status' => '1',
            'account_title' => 'Test Account',
            'account_type' => 'L0',
            'is_pin_set' => '0',
            'success' => true,
        ];

        $verification = $this->repository->create($data);

        $this->assertInstanceOf(AccountVerification::class, $verification);
        $this->assertEquals('000001', $verification->trace_no);
        $this->assertEquals('1234567890123', $verification->cnic);
        $this->assertEquals('03001234567', $verification->mobile_no);
        $this->assertTrue($verification->success);
        $this->assertDatabaseHas('zindagi_zconnect_account_verifications', [
            'trace_no' => '000001',
            'cnic' => '1234567890123',
        ]);
    }

    /**
     * Test finding account verification by trace number.
     */
    public function test_find_by_trace_no(): void
    {
        AccountVerification::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        $verification = $this->repository->findByTraceNo('000001');

        $this->assertInstanceOf(AccountVerification::class, $verification);
        $this->assertEquals('000001', $verification->trace_no);
        $this->assertEquals('1234567890123', $verification->cnic);
    }

    /**
     * Test finding account verification by trace number when not exists.
     */
    public function test_find_by_trace_no_not_exists(): void
    {
        $verification = $this->repository->findByTraceNo('999999');

        $this->assertNull($verification);
    }

    /**
     * Test finding account verification by CNIC.
     */
    public function test_find_by_cnic(): void
    {
        AccountVerification::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        // Sleep for 1 second to ensure different timestamps
        sleep(1);

        AccountVerification::create([
            'trace_no' => '000002',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234568',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        $verification = $this->repository->findByCnic('1234567890123');

        $this->assertInstanceOf(AccountVerification::class, $verification);
        $this->assertEquals('1234567890123', $verification->cnic);
        // Should return the latest one
        $this->assertEquals('000002', $verification->trace_no);
    }

    /**
     * Test finding account verification by CNIC when not exists.
     */
    public function test_find_by_cnic_not_exists(): void
    {
        $verification = $this->repository->findByCnic('9999999999999');

        $this->assertNull($verification);
    }

    /**
     * Test creating account verification with JSON data.
     */
    public function test_create_with_json_data(): void
    {
        $data = [
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => [
                'cnic' => '1234567890123',
                'mobile_no' => '03001234567',
            ],
            'response_data' => [
                'VerifyAccLinkAccResponse' => [
                    'ResponseCode' => '00',
                    'AccountStatus' => '1',
                ],
            ],
            'success' => true,
        ];

        $verification = $this->repository->create($data);

        $this->assertIsArray($verification->request_data);
        $this->assertIsArray($verification->response_data);
        $this->assertEquals('1234567890123', $verification->request_data['cnic']);
    }

    /**
     * Test creating account verification with null optional fields.
     */
    public function test_create_with_null_optional_fields(): void
    {
        $data = [
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => null,
            'response_data' => null,
            'response_code' => null,
            'account_status' => null,
            'account_title' => null,
            'account_type' => null,
            'is_pin_set' => null,
            'success' => false,
        ];

        $verification = $this->repository->create($data);

        $this->assertInstanceOf(AccountVerification::class, $verification);
        $this->assertFalse($verification->success);
    }
}

