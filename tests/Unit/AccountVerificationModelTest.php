<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Models\AccountVerification;

class AccountVerificationModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test model creation with all fields.
     */
    public function test_model_creation_with_all_fields(): void
    {
        $verification = AccountVerification::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => ['test' => 'request'],
            'response_data' => ['test' => 'response'],
            'response_code' => '00',
            'account_status' => '1',
            'account_title' => 'Test Account',
            'account_type' => 'L0',
            'is_pin_set' => '0',
            'success' => true,
        ]);

        $this->assertInstanceOf(AccountVerification::class, $verification);
        $this->assertEquals('000001', $verification->trace_no);
        $this->assertTrue($verification->success);
        $this->assertIsArray($verification->request_data);
        $this->assertIsArray($verification->response_data);
    }

    /**
     * Test model JSON casting.
     */
    public function test_model_json_casting(): void
    {
        $requestData = ['cnic' => '1234567890123', 'mobile_no' => '03001234567'];
        $responseData = ['ResponseCode' => '00', 'AccountStatus' => '1'];

        $verification = AccountVerification::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'request_data' => $requestData,
            'response_data' => $responseData,
            'success' => true,
        ]);

        $this->assertIsArray($verification->request_data);
        $this->assertEquals('1234567890123', $verification->request_data['cnic']);
        $this->assertIsArray($verification->response_data);
        $this->assertEquals('00', $verification->response_data['ResponseCode']);
    }

    /**
     * Test model boolean casting.
     */
    public function test_model_boolean_casting(): void
    {
        $verification = AccountVerification::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        $this->assertIsBool($verification->success);
        $this->assertTrue($verification->success);

        $verification->success = false;
        $verification->save();

        $this->assertFalse($verification->success);
    }

    /**
     * Test model datetime casting.
     */
    public function test_model_datetime_casting(): void
    {
        $verification = AccountVerification::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $verification->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $verification->updated_at);
    }

    /**
     * Test model with null optional fields.
     */
    public function test_model_with_null_optional_fields(): void
    {
        $verification = AccountVerification::create([
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
        ]);

        $this->assertNull($verification->request_data);
        $this->assertNull($verification->response_data);
        $this->assertNull($verification->response_code);
        $this->assertFalse($verification->success);
    }

    /**
     * Test model mass assignment protection.
     */
    public function test_model_mass_assignment(): void
    {
        $verification = AccountVerification::create([
            'trace_no' => '000001',
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'success' => true,
        ]);

        // Should not throw exception for fillable fields
        $this->assertEquals('000001', $verification->trace_no);
    }

    /**
     * Test model table name.
     */
    public function test_model_table_name(): void
    {
        $verification = new AccountVerification();
        $this->assertEquals('zindagi_zconnect_account_verifications', $verification->getTable());
    }
}

