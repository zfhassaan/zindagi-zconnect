<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountLinking;

class AccountLinkingModelTest extends TestCase
{

    /**
     * Test model creation with all fields.
     */
    public function test_model_creation_with_all_fields(): void
    {
        $linking = AccountLinking::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => ['test' => 'request'],
            'response_data' => ['test' => 'response'],
            'response_code' => '00',
            'account_title' => 'Test Account',
            'account_type' => 'Level0',
            'otp_pin' => '123456',
            'success' => true,
        ]);

        $this->assertInstanceOf(AccountLinking::class, $linking);
        $this->assertEquals('000001', $linking->trace_no);
        $this->assertTrue($linking->success);
        $this->assertIsArray($linking->request_data);
        $this->assertIsArray($linking->response_data);
    }

    /**
     * Test model JSON casting.
     */
    public function test_model_json_casting(): void
    {
        $requestData = ['cnic' => '1234567890123', 'mobile_no' => '03001234567'];
        $responseData = ['ResponseCode' => '00', 'AccountTitle' => 'Test Account'];

        $linking = AccountLinking::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => $requestData,
            'response_data' => $responseData,
            'success' => true,
        ]);

        $this->assertIsArray($linking->request_data);
        $this->assertEquals('1234567890123', $linking->request_data['cnic']);
        $this->assertIsArray($linking->response_data);
        $this->assertEquals('00', $linking->response_data['ResponseCode']);
    }

    /**
     * Test model boolean casting.
     */
    public function test_model_boolean_casting(): void
    {
        $linking = AccountLinking::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        $this->assertIsBool($linking->success);
        $this->assertTrue($linking->success);

        $linking->success = false;
        $linking->save();

        $this->assertFalse($linking->success);
    }

    /**
     * Test model datetime casting.
     */
    public function test_model_datetime_casting(): void
    {
        $linking = AccountLinking::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $linking->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $linking->updated_at);
    }

    /**
     * Test model with null optional fields.
     */
    public function test_model_with_null_optional_fields(): void
    {
        $linking = AccountLinking::create([
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
        ]);

        $this->assertNull($linking->request_data);
        $this->assertNull($linking->response_data);
        $this->assertNull($linking->response_code);
        $this->assertFalse($linking->success);
    }

    /**
     * Test model mass assignment protection.
     */
    public function test_model_mass_assignment(): void
    {
        $linking = AccountLinking::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        // Should not throw exception for fillable fields
        $this->assertEquals('000001', $linking->trace_no);
    }

    /**
     * Test model table name.
     */
    public function test_model_table_name(): void
    {
        $linking = new AccountLinking();
        $this->assertEquals('zindagi_zconnect_account_linkings', $linking->getTable());
    }
}

