<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountVerificationResponseDTO;

class AccountVerificationResponseDTOTest extends TestCase
{
    /**
     * Test successful response parsing.
     */
    public function test_successful_response_parsing(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000009',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountStatus' => '1',
                'AccountTitle' => 'MUHAMMAD ARSALAN KHAN',
                'AccountType' => 'L0',
                'Cnic' => '1234567890123',
                'IsPinSet' => '0',
                'MobileNumber' => '03001234567',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Account exists'],
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('0088', $dto->merchantType);
        $this->assertEquals('000009', $dto->traceNo);
        $this->assertEquals('NOVA', $dto->companyName);
        $this->assertEquals('20210105201527', $dto->dateTime);
        $this->assertEquals('1', $dto->accountStatus);
        $this->assertEquals('MUHAMMAD ARSALAN KHAN', $dto->accountTitle);
        $this->assertEquals('L0', $dto->accountType);
        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('0', $dto->isPinSet);
        $this->assertEquals('03001234567', $dto->mobileNumber);
        $this->assertEquals(['Account exists'], $dto->responseDetails);
        $this->assertEquals('Account exists', $dto->message);
    }

    /**
     * Test failed response parsing.
     */
    public function test_failed_response_parsing(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '01',
                'ResponseDetails' => ['Account not found'],
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
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

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
        $this->assertEquals('4001', $dto->errorCode);
    }

    /**
     * Test account exists check.
     */
    public function test_account_exists_check(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
                'AccountStatus' => '1',
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->accountExists());
    }

    /**
     * Test account does not exist.
     */
    public function test_account_does_not_exist(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
                'AccountStatus' => '0',
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->accountExists());
    }

    /**
     * Test account exists with non-00 response code.
     */
    public function test_account_exists_with_non_zero_response_code(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '01',
                'AccountStatus' => '1',
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->accountExists());
    }

    /**
     * Test PIN set check.
     */
    public function test_pin_set_check(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'IsPinSet' => '1',
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->isPinSet());
    }

    /**
     * Test PIN not set.
     */
    public function test_pin_not_set(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'IsPinSet' => '0',
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->isPinSet());
    }

    /**
     * Test PIN set with value 00.
     */
    public function test_pin_set_with_value_00(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'IsPinSet' => '00',
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->isPinSet());
    }

    /**
     * Test toArray format.
     */
    public function test_to_array_format(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000009',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountStatus' => '1',
                'AccountTitle' => 'MUHAMMAD ARSALAN KHAN',
                'AccountType' => 'L0',
                'Cnic' => '1234567890123',
                'IsPinSet' => '0',
                'MobileNumber' => '03001234567',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Account exists'],
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);
        $array = $dto->toArray();

        $this->assertTrue($array['success']);
        $this->assertEquals('00', $array['response_code']);
        $this->assertEquals('0088', $array['merchant_type']);
        $this->assertEquals('000009', $array['trace_no']);
        $this->assertEquals('NOVA', $array['company_name']);
        $this->assertEquals('20210105201527', $array['date_time']);
        $this->assertEquals('1', $array['account_status']);
        $this->assertEquals('MUHAMMAD ARSALAN KHAN', $array['account_title']);
        $this->assertEquals('L0', $array['account_type']);
        $this->assertEquals('1234567890123', $array['cnic']);
        $this->assertEquals('0', $array['is_pin_set']);
        $this->assertEquals('03001234567', $array['mobile_number']);
        $this->assertEquals(['Account exists'], $array['response_details']);
        $this->assertEquals('Account exists', $array['message']);
        $this->assertTrue($array['account_exists']);
    }

    /**
     * Test response with missing fields.
     */
    public function test_response_with_missing_fields(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertTrue($dto->success);
        $this->assertNull($dto->accountStatus);
        $this->assertNull($dto->accountTitle);
        $this->assertNull($dto->accountType);
    }

    /**
     * Test unknown error response.
     */
    public function test_unknown_error_response(): void
    {
        $response = [];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertFalse($dto->success);
        $this->assertEquals('Unknown error', $dto->message);
    }

    /**
     * Test response with empty ResponseDetails.
     */
    public function test_response_with_empty_response_details(): void
    {
        $response = [
            'VerifyAccLinkAccResponse' => [
                'ResponseCode' => '00',
                'ResponseDetails' => [],
            ],
        ];

        $dto = AccountVerificationResponseDTO::fromApiResponse($response);

        $this->assertNull($dto->message);
    }
}

