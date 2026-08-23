<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\L2AccountUpgradeResponseDTO;

class L2AccountUpgradeResponseDTOTest extends TestCase
{
    public function test_from_api_response_with_complete_wrapper(): void
    {
        $dto = L2AccountUpgradeResponseDTO::fromApiResponse([
            'l2AccountUpgradeRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'Rrn' => '123456789012',
                'TransactionId' => 'TXN-99',
                'HashData' => 'abc',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Successful', $dto->message);
        $this->assertEquals('TXN-99', $dto->transactionId);
        $this->assertEquals('abc', $dto->hashData);
        $this->assertNull($dto->errorCode);
    }

    public function test_from_api_response_with_business_error(): void
    {
        $dto = L2AccountUpgradeResponseDTO::fromApiResponse([
            'l2AccountUpgradeRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Biometric mismatch',
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->errorCode);
        $this->assertEquals('Biometric mismatch', $dto->message);
    }

    public function test_from_api_response_with_gateway_error(): void
    {
        $dto = L2AccountUpgradeResponseDTO::fromApiResponse([
            'messages' => 'Unauthorized',
            'errorcode' => '401',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('401', $dto->errorCode);
        $this->assertEquals('Unauthorized', $dto->message);
    }

    public function test_to_array_includes_mapped_fields(): void
    {
        $dto = L2AccountUpgradeResponseDTO::fromArray([
            'l2AccountUpgradeRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'TransactionId' => 'TXN-1',
            ],
        ]);

        $array = $dto->toArray();

        $this->assertTrue($array['success']);
        $this->assertEquals('TXN-1', $array['transaction_id']);
        $this->assertEquals('Successful', $array['response_description']);
    }
}
