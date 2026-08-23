<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountInfoResponseDTO;

class AccountInfoResponseDTOTest extends TestCase
{
    public function test_from_api_response_with_complete_wrapper(): void
    {
        $dto = AccountInfoResponseDTO::fromApiResponse([
            'accountInfoRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'ResponseDateTime' => '20250116174253',
                'DateOfBirth' => '19900101',
                'AccountLevelCode' => 'L1',
                'Email' => 'user@example.com',
                'Cnic' => '1234567890123',
                'Segment' => 'RETAIL',
                'Rrn' => '12345678901234',
                'AccountNumber' => '001122334455',
                'AccountNatureCode' => 'SAV',
                'AccountTitle' => 'JOHN DOE',
                'AccountStatusCode' => 'A',
                'RegistrationTypeCode' => '01',
                'HashData' => 'abc123hash',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Successful', $dto->message);
        $this->assertEquals('19900101', $dto->dateOfBirth);
        $this->assertEquals('user@example.com', $dto->email);
        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('001122334455', $dto->accountNumber);
        $this->assertEquals('JOHN DOE', $dto->accountTitle);
        $this->assertEquals('abc123hash', $dto->hashData);
        $this->assertNull($dto->errorCode);
    }

    public function test_from_array_handles_unwrapped_response(): void
    {
        $dto = AccountInfoResponseDTO::fromArray([
            'ResponseCode' => '00',
            'ResponseDescription' => 'Successful',
            'AccountTitle' => 'JANE DOE',
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('JANE DOE', $dto->accountTitle);
    }

    public function test_from_api_response_with_business_error_code(): void
    {
        $dto = AccountInfoResponseDTO::fromApiResponse([
            'accountInfoRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Account not found',
                'Rrn' => '12345678901234',
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('01', $dto->errorCode);
        $this->assertEquals('Account not found', $dto->message);
    }

    public function test_from_api_response_with_gateway_error(): void
    {
        $dto = AccountInfoResponseDTO::fromApiResponse([
            'messages' => 'Unauthorized',
            'errorcode' => '401',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('401', $dto->responseCode);
        $this->assertEquals('401', $dto->errorCode);
        $this->assertEquals('Unauthorized', $dto->message);
    }

    public function test_to_array_includes_mapped_fields(): void
    {
        $dto = AccountInfoResponseDTO::fromApiResponse([
            'accountInfoRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'AccountNumber' => '001122334455',
            ],
        ]);

        $array = $dto->toArray();

        $this->assertTrue($array['success']);
        $this->assertEquals('00', $array['response_code']);
        $this->assertEquals('001122334455', $array['account_number']);
        $this->assertEquals('Successful', $array['response_description']);
    }
}
