<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Repositories\AccountLinkingRepository;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;

class AccountLinkingRepositoryTest extends TestCase
{

    protected AccountLinkingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AccountLinkingRepository();
    }

    /**
     * Test creating account linking record.
     */
    public function test_create_account_linking(): void
    {
        $data = [
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => ['test' => 'data'],
            'response_data' => ['test' => 'response'],
            'response_code' => '00',
            'account_title' => 'Test Account',
            'account_type' => 'Level0',
            'otp_pin' => '123456',
            'success' => true,
        ];

        $linking = $this->repository->create($data);

        $this->assertInstanceOf(AccountLinking::class, $linking);
        $this->assertEquals('000001', $linking->trace_no);
        $this->assertEquals('1234567890123', $linking->cnic);
        $this->assertEquals('03001234567', $linking->mobile_no);
        $this->assertTrue($linking->success);
        $this->assertDatabaseHas('zindagi_zconnect_account_linkings', [
            'trace_no' => '000001',
            'cnic' => '1234567890123',
        ]);
    }

    /**
     * Test finding account linking by trace number.
     */
    public function test_find_by_trace_no(): void
    {
        AccountLinking::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        $linking = $this->repository->findByTraceNo('000001');

        $this->assertInstanceOf(AccountLinking::class, $linking);
        $this->assertEquals('000001', $linking->trace_no);
        $this->assertEquals('1234567890123', $linking->cnic);
    }

    /**
     * Test finding account linking by trace number when not exists.
     */
    public function test_find_by_trace_no_not_exists(): void
    {
        $linking = $this->repository->findByTraceNo('999999');

        $this->assertNull($linking);
    }

    /**
     * Test finding account linking by CNIC.
     */
    public function test_find_by_cnic(): void
    {
        AccountLinking::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
            'created_at' => now()->subDay(),
        ]);

        AccountLinking::create([
            'trace_no' => '000002',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234568',
            'merchant_type' => '0088',
            'success' => true,
            'created_at' => now(),
        ]);

        $linking = $this->repository->findByCnic('1234567890123');

        $this->assertInstanceOf(AccountLinking::class, $linking);
        $this->assertEquals('1234567890123', $linking->cnic);
        // Should return the latest one
        $this->assertEquals('000002', $linking->trace_no);
    }

    /**
     * Test finding account linking by CNIC when not exists.
     */
    public function test_find_by_cnic_not_exists(): void
    {
        $linking = $this->repository->findByCnic('9999999999999');

        $this->assertNull($linking);
    }

    /**
     * Test creating account linking with JSON data.
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
                'LinkAccountResponse' => [
                    'ResponseCode' => '00',
                    'AccountTitle' => 'Test Account',
                ],
            ],
            'success' => true,
        ];

        $linking = $this->repository->create($data);

        $this->assertIsArray($linking->request_data);
        $this->assertIsArray($linking->response_data);
        $this->assertEquals('1234567890123', $linking->request_data['cnic']);
    }

    /**
     * Test creating account linking with null optional fields.
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
            'account_title' => null,
            'account_type' => null,
            'otp_pin' => null,
            'success' => false,
        ];

        $linking = $this->repository->create($data);

        $this->assertInstanceOf(AccountLinking::class, $linking);
        $this->assertFalse($linking->success);
    }
}

