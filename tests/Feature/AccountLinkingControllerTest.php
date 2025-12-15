<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO;

class AccountLinkingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful account linking endpoint.
     */
    public function test_link_account_endpoint_success(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        
        $successResponse = new AccountLinkingResponseDTO(
            success: true,
            responseCode: '00',
            merchantType: '0088',
            traceNo: '000001',
            companyName: 'NOVA',
            dateTime: '20210105201527',
            accountTitle: 'MUHAMMADARSALANKHAN',
            accountType: 'Level0',
            responseDetails: ['Successful']
        );

        $mockService->shouldReceive('linkAccount')
            ->once()
            ->andReturn($successResponse);

        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        $request = \Illuminate\Http\Request::create('/api/onboarding/link-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
        ]);

        $response = $controller->linkAccount($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('00', $responseData['response_code']);
        $this->assertEquals('MUHAMMADARSALANKHAN', $responseData['account_title']);
    }

    /**
     * Test account linking endpoint with validation errors.
     */
    public function test_link_account_endpoint_validation_errors(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/link-account', 'POST', [
            'cnic' => '123456789012', // Invalid length
            'mobile_no' => '0300123456', // Invalid length
        ]);

        try {
            $controller->linkAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('cnic', $e->errors());
            $this->assertArrayHasKey('mobile_no', $e->errors());
        }
    }

    /**
     * Test account linking endpoint with missing required fields.
     */
    public function test_link_account_endpoint_missing_required_fields(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/link-account', 'POST', []);

        try {
            $controller->linkAccount($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('cnic', $e->errors());
            $this->assertArrayHasKey('mobile_no', $e->errors());
        }
    }

    /**
     * Test account linking endpoint with optional fields.
     */
    public function test_link_account_endpoint_with_optional_fields(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        
        $successResponse = new AccountLinkingResponseDTO(
            success: true,
            responseCode: '00',
            accountTitle: 'Test Account'
        );

        $mockService->shouldReceive('linkAccount')
            ->once()
            ->andReturn($successResponse);

        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/link-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
            'merchant_type' => '0088',
            'trace_no' => '000001',
            'date_time' => '20210105201527',
            'company_name' => 'NOVA',
            'transaction_type' => '01',
            'reserved1' => '02',
            'otp_pin' => '123456',
        ]);

        $response = $controller->linkAccount($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test account linking endpoint with failed response.
     */
    public function test_link_account_endpoint_failed_response(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        
        $failedResponse = new AccountLinkingResponseDTO(
            success: false,
            responseCode: '01',
            message: 'Account linking failed',
            errorCode: '4005'
        );

        $mockService->shouldReceive('linkAccount')
            ->once()
            ->andReturn($failedResponse);

        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/link-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
        ]);

        $response = $controller->linkAccount($request);
        
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('01', $responseData['response_code']);
        $this->assertEquals('Account linking failed', $responseData['message']);
    }

    /**
     * Test account linking endpoint without OtpPin.
     */
    public function test_link_account_endpoint_without_otp_pin(): void
    {
        $mockService = Mockery::mock(OnboardingServiceInterface::class);
        
        $successResponse = new AccountLinkingResponseDTO(
            success: true,
            responseCode: '00'
        );

        $mockService->shouldReceive('linkAccount')
            ->once()
            ->andReturn($successResponse);

        $controller = new \zfhassaan\ZindagiZconnect\Modules\Onboarding\Controllers\OnboardingController($mockService);
        
        $request = \Illuminate\Http\Request::create('/api/onboarding/link-account', 'POST', [
            'cnic' => '1234567890123',
            'mobile_no' => '03001234567',
        ]);

        $response = $controller->linkAccount($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}

