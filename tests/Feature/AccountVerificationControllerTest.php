<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;

class AccountVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful account verification endpoint.
     * 
     * Note: This test requires routes to be defined in your application.
     * Add route: Route::post('/api/onboarding/verify-account', [OnboardingController::class, 'verifyAccount']);
     */
    public function test_verify_account_endpoint_success(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        
        $successResponse = new AccountVerificationResponseDTO(
            success: true,
            responseCode: '00',
            merchantType: '0088',
            traceNo: '000009',
            companyName: 'NOVA',
            dateTime: '20210105201527',
            accountStatus: '1',
            accountTitle: 'MUHAMMAD ARSALAN KHAN',
            accountType: 'L0',
            cnic: '1234567890123',
            isPinSet: '0',
            mobileNumber: '03001234567',
            responseDetails: ['Account exists']
        );

        $mockService->shouldReceive('verifyAccount')
            ->once()
            ->andReturn($successResponse);

        $this->app->instance(OnboardingServiceInterface::class, $mockService);

        // Test controller directly
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
        ]);

        $response = $controller->verifyAccount($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('00', $responseData['response_code']);
        $this->assertEquals('1', $responseData['account_status']);
        $this->assertEquals('MUHAMMAD ARSALAN KHAN', $responseData['account_title']);
    }

    /**
     * Test account verification endpoint with validation errors.
     */
    public function test_verify_account_endpoint_validation_errors(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '123456789012', // Invalid length
            'mobile_no' => '0300123456', // Invalid length
        ]);

        try {
            $controller->verifyAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('cnic', $e->errors());
            $this->assertArrayHasKey('mobile_no', $e->errors());
        }
    }

    /**
     * Test account verification endpoint with missing required fields.
     */
    public function test_verify_account_endpoint_missing_required_fields(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', []);

        try {
            $controller->verifyAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('cnic', $e->errors());
            $this->assertArrayHasKey('mobile_no', $e->errors());
        }
    }

    /**
     * Test account verification endpoint with invalid CNIC format.
     */
    public function test_verify_account_endpoint_invalid_cnic_format(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '12345678901234', // 14 characters
            'mobile_no' => '03001234567',
        ]);

        try {
            $controller->verifyAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('cnic', $e->errors());
        }
    }

    /**
     * Test account verification endpoint with invalid mobile number format.
     */
    public function test_verify_account_endpoint_invalid_mobile_format(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '030012345678', // 12 characters
        ]);

        try {
            $controller->verifyAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('mobile_no', $e->errors());
        }
    }

    /**
     * Test account verification endpoint with optional fields.
     */
    public function test_verify_account_endpoint_with_optional_fields(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        
        $successResponse = new AccountVerificationResponseDTO(
            success: true,
            responseCode: '00',
            accountStatus: '1'
        );

        $mockService->shouldReceive('verifyAccount')
            ->once()
            ->andReturn($successResponse);

        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'trace_no' => '000009',
            'date_time' => '20210105201527',
            'company_name' => 'NOVA',
            'reserved1' => '01',
            'reserved2' => '01',
            'transaction_type' => '02',
        ]);

        $response = $controller->verifyAccount($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test account verification endpoint with failed response.
     */
    public function test_verify_account_endpoint_failed_response(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        
        $failedResponse = new AccountVerificationResponseDTO(
            success: false,
            responseCode: '01',
            message: 'Account not found',
            errorCode: '4005'
        );

        $mockService->shouldReceive('verifyAccount')
            ->once()
            ->andReturn($failedResponse);

        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
        ]);

        $response = $controller->verifyAccount($request);
        
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('01', $responseData['response_code']);
        $this->assertEquals('Account not found', $responseData['message']);
    }

    /**
     * Test account verification endpoint with invalid optional field lengths.
     */
    public function test_verify_account_endpoint_invalid_optional_field_lengths(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '088', // Invalid length
            'trace_no' => '00009', // Invalid length
            'date_time' => '2021010520152', // Invalid length
            'company_name' => 'NOV', // Invalid length
            'reserved1' => '0', // Invalid length
            'reserved2' => '0', // Invalid length
            'transaction_type' => '0', // Invalid length
        ]);

        try {
            $controller->verifyAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('merchant_type', $errors);
            $this->assertArrayHasKey('trace_no', $errors);
            $this->assertArrayHasKey('date_time', $errors);
            $this->assertArrayHasKey('company_name', $errors);
            $this->assertArrayHasKey('reserved1', $errors);
            $this->assertArrayHasKey('reserved2', $errors);
            $this->assertArrayHasKey('transaction_type', $errors);
        }
    }

    /**
     * Test account verification endpoint with non-string values.
     */
    public function test_verify_account_endpoint_non_string_values(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => 1234567890123, // Integer instead of string
            'mobile_no' => 3001234567, // Integer instead of string
        ]);

        try {
            $controller->verifyAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('cnic', $errors);
            $this->assertArrayHasKey('mobile_no', $errors);
        }
    }

    /**
     * Test account verification endpoint with special characters.
     */
    public function test_verify_account_endpoint_special_characters(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $mockService->shouldReceive('verifyAccount')
            ->once()
            ->andReturn(new AccountVerificationResponseDTO(
                success: false,
                responseCode: '',
                message: 'CNIC must be exactly 13 characters'
            ));

        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/verify-account', 'POST', [
            'cnic' => '12345-6789012-3', // With dashes - 15 chars total
            'mobile_no' => '0300-1234567', // With dash - 12 chars total
        ]);

        // Will pass validation but fail at service level
        $response = $controller->verifyAccount($request);
        $this->assertEquals(400, $response->getStatusCode());
    }
}

