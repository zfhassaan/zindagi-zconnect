<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\MinorAccountVerificationResponseDTO;

class MinorAccountVerificationResponseDTOTest extends TestCase
{
    public function test_from_api_response_with_camel_case_wrapper(): void
    {
        $dto = MinorAccountVerificationResponseDTO::fromApiResponse([
            'minorAccountVerifyRes' => [
                'responseCode' => '00',
                'responseDescription' => 'Successful',
                'hashData' => 'some_hash',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Successful', $dto->message);
        $this->assertEquals('some_hash', $dto->hashData);
        $this->assertNull($dto->errorCode);
    }

    public function test_from_api_response_with_business_error(): void
    {
        $dto = MinorAccountVerificationResponseDTO::fromApiResponse([
            'minorAccountVerifyRes' => [
                'responseCode' => '24',
                'responseDescription' => 'CNIC is already in use',
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('24', $dto->errorCode);
        $this->assertEquals('CNIC is already in use', $dto->message);
    }

    public function test_from_api_response_with_gateway_error(): void
    {
        $dto = MinorAccountVerificationResponseDTO::fromApiResponse([
            'messages' => 'Unauthorized',
            'errorcode' => '401',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('401', $dto->errorCode);
        $this->assertEquals('Unauthorized', $dto->message);
    }

    public function test_to_array_includes_mapped_fields(): void
    {
        $dto = MinorAccountVerificationResponseDTO::fromArray([
            'minorAccountVerifyRes' => [
                'responseCode' => '00',
                'responseDescription' => 'Successful',
            ],
        ]);

        $array = $dto->toArray();

        $this->assertTrue($array['success']);
        $this->assertEquals('00', $array['response_code']);
        $this->assertEquals('Successful', $array['response_description']);
    }
}
