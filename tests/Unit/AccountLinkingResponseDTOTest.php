<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingResponseDTO;

class AccountLinkingResponseDTOTest extends TestCase
{
    /**
     * Test successful response parsing.
     */
    public function test_successful_response_parsing(): void
    {
        $response = [
            'LinkAccountResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000001',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountTitle' => 'MUHAMMADARSALANKHAN',
                'AccountType' => 'Level0',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
            ],
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('000001', $dto->traceNo);
        $this->assertEquals('NOVA', $dto->companyName);
        $this->assertEquals('20210105201527', $dto->dateTime);
        $this->assertEquals('MUHAMMADARSALANKHAN', $dto->accountTitle);
        $this->assertEquals('Level0', $dto->accountType);
        $this->assertEquals(['Successful'], $dto->responseDetails);
        $this->assertEquals('Successful', $dto->message);
    }

    /**
     * Test failed response parsing.
     */
    public function test_failed_response_parsing(): void
    {
        $response = [
            'LinkAccountResponse' => [
                'ResponseCode' => '01',
                'ResponseDetails' => ['Account linking failed'],
            ],
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('Account linking failed', $dto->message);
    }

    /**
     * Test error response parsing.
     */
    public function test_error_response_parsing(): void
    {
        $response = [
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('', $dto->responseCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
        $this->assertEquals('4001', $dto->errorCode);
    }

    /**
     * Test fromErrorResponse method.
     */
    public function test_from_error_response_method(): void
    {
        $error = [
            'messages' => 'Record Not Found',
            'errorcode' => '4005',
        ];

        $dto = AccountLinkingResponseDTO::fromErrorResponse($error, '4005');

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('Record Not Found', $dto->message);
        $this->assertEquals('4005', $dto->errorCode);
    }

    /**
     * Test array format conversion.
     */
    public function test_array_format_conversion(): void
    {
        $response = [
            'LinkAccountResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000001',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountTitle' => 'MUHAMMADARSALANKHAN',
                'AccountType' => 'Level0',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
            ],
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);
        $array = $dto->toArray();

        $this->assertTrue($array['success']);
        $this->assertEquals('00', $array['response_code']);
        $this->assertEquals('0088', $array['merchant_type']);
        $this->assertEquals('000001', $array['trace_no']);
        $this->assertEquals('NOVA', $array['company_name']);
        $this->assertEquals('20210105201527', $array['date_time']);
        $this->assertEquals('MUHAMMADARSALANKHAN', $array['account_title']);
        $this->assertEquals('Level0', $array['account_type']);
        $this->assertEquals(['Successful'], $array['response_details']);
        $this->assertEquals('Successful', $array['message']);
    }

    /**
     * Test missing fields handling.
     */
    public function test_missing_fields_handling(): void
    {
        $response = [
            'LinkAccountResponse' => [
                'ResponseCode' => '00',
            ],
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertNull($dto->merchantType);
        $this->assertNull($dto->traceNo);
        $this->assertNull($dto->accountTitle);
    }

    /**
     * Test unknown error response.
     */
    public function test_unknown_error_response(): void
    {
        $response = [
            'SomeOtherKey' => 'value',
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('Unknown error', $dto->message);
    }

    /**
     * Test empty response details.
     */
    public function test_empty_response_details(): void
    {
        $response = [
            'LinkAccountResponse' => [
                'ResponseCode' => '00',
                'ResponseDetails' => [],
            ],
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('Account linked successfully', $dto->message);
        $this->assertEquals([], $dto->responseDetails);
    }

    /**
     * Test response with null response details.
     */
    public function test_response_with_null_response_details(): void
    {
        $response = [
            'LinkAccountResponse' => [
                'ResponseCode' => '00',
            ],
        ];

        $dto = AccountLinkingResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('Account linked successfully', $dto->message);
    }
}

