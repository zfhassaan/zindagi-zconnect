<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountOpeningResponseDTO;

class AccountOpeningResponseDTOTest extends TestCase
{
    /**
     * Test successful response parsing.
     */
    public function test_successful_response_parsing(): void
    {
        $response = [
            'AccountOpeningResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000011',
                'CompanyName' => 'NOVA',
                'DateTime' => '20220117115415',
                'MobileNetwork' => 'UFONE',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
            ],
        ];

        $dto = AccountOpeningResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('000011', $dto->traceNo);
        $this->assertEquals('NOVA', $dto->companyName);
        $this->assertEquals('20220117115415', $dto->dateTime);
        $this->assertEquals('UFONE', $dto->mobileNetwork);
        $this->assertEquals(['Successful'], $dto->responseDetails);
        $this->assertEquals('Successful', $dto->message);
    }

    /**
     * Test failed response parsing.
     */
    public function test_failed_response_parsing(): void
    {
        $response = [
            'AccountOpeningResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000011',
                'CompanyName' => 'NOVA',
                'DateTime' => '20220117115415',
                'MobileNetwork' => 'UFONE',
                'ResponseCode' => '01',
                'ResponseDetails' => ['Account opening failed'],
            ],
        ];

        $dto = AccountOpeningResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('Account opening failed', $dto->message);
    }

    /**
     * Test error response parsing.
     */
    public function test_error_response_parsing(): void
    {
        $response = [
            'messages' => 'Invalid request',
            'errorcode' => '4001',
        ];

        $dto = AccountOpeningResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('', $dto->responseCode);
        $this->assertEquals('Invalid request', $dto->message);
        $this->assertEquals('4001', $dto->errorCode);
    }

    /**
     * Test from error response method.
     */
    public function test_from_error_response_method(): void
    {
        $error = [
            'messages' => 'Validation failed',
            'errorcode' => '4002',
        ];

        $dto = AccountOpeningResponseDTO::fromErrorResponse($error, '4002');

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('Validation failed', $dto->message);
        $this->assertEquals('4002', $dto->errorCode);
    }

    /**
     * Test array format conversion.
     */
    public function test_array_format_conversion(): void
    {
        $dto = new AccountOpeningResponseDTO(
            success: true,
            responseCode: '00',
            merchantType: '0088',
            traceNo: '000011',
            companyName: 'NOVA',
            dateTime: '20220117115415',
            mobileNetwork: 'UFONE',
            responseDetails: ['Successful'],
            message: 'Successful'
        );

        $array = $dto->toArray();

        $this->assertTrue($array['success']);
        $this->assertEquals('00', $array['response_code']);
        $this->assertEquals('0088', $array['merchant_type']);
        $this->assertEquals('000011', $array['trace_no']);
        $this->assertEquals('NOVA', $array['company_name']);
        $this->assertEquals('20220117115415', $array['date_time']);
        $this->assertEquals('UFONE', $array['mobile_network']);
        $this->assertEquals(['Successful'], $array['response_details']);
        $this->assertEquals('Successful', $array['message']);
    }

    /**
     * Test missing fields handling.
     */
    public function test_missing_fields_handling(): void
    {
        $response = [
            'AccountOpeningResponse' => [
                'ResponseCode' => '00',
            ],
        ];

        $dto = AccountOpeningResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertNull($dto->merchantType);
        $this->assertNull($dto->traceNo);
        $this->assertNull($dto->companyName);
        $this->assertNull($dto->dateTime);
        $this->assertNull($dto->mobileNetwork);
        $this->assertEquals([], $dto->responseDetails);
    }

    /**
     * Test unknown error response.
     */
    public function test_unknown_error_response(): void
    {
        $response = [
            'SomeOtherKey' => 'value',
        ];

        $dto = AccountOpeningResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('', $dto->responseCode);
        $this->assertEquals('Unknown error', $dto->message);
    }

    /**
     * Test response with empty response details.
     */
    public function test_response_with_empty_response_details(): void
    {
        $response = [
            'AccountOpeningResponse' => [
                'ResponseCode' => '00',
                'ResponseDetails' => [],
            ],
        ];

        $dto = AccountOpeningResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals([], $dto->responseDetails);
        $this->assertEquals('Account opened successfully', $dto->message);
    }
}

