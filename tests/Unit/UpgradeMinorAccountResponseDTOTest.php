<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\UpgradeMinorAccountResponseDTO;

class UpgradeMinorAccountResponseDTOTest extends TestCase
{
    public function test_from_api_response_with_pascal_case_wrapper(): void
    {
        $dto = UpgradeMinorAccountResponseDTO::fromApiResponse([
            'upgradeMinorAccountRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'HashData' => 'some_hash',
                'Rrn' => '0090909998881',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Successful', $dto->message);
        $this->assertEquals('some_hash', $dto->hashData);
        $this->assertEquals('0090909998881', $dto->rrn);
        $this->assertNull($dto->errorCode);
    }

    public function test_from_api_response_with_business_error(): void
    {
        $dto = UpgradeMinorAccountResponseDTO::fromApiResponse([
            'upgradeMinorAccountRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Account not eligible',
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->errorCode);
        $this->assertEquals('Account not eligible', $dto->message);
    }

    public function test_from_api_response_with_gateway_error(): void
    {
        $dto = UpgradeMinorAccountResponseDTO::fromApiResponse([
            'messages' => 'Unauthorized',
            'errorcode' => '401',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('401', $dto->errorCode);
        $this->assertEquals('Unauthorized', $dto->message);
    }

    public function test_to_array_includes_mapped_fields(): void
    {
        $dto = UpgradeMinorAccountResponseDTO::fromArray([
            'upgradeMinorAccountRes' => [
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
