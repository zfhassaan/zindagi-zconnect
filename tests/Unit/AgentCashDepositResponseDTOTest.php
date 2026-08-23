<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashDepositResponseDTO;

class AgentCashDepositResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentCashDepositResponseDTO::fromApiResponse([
            'agentCashDepositRes' => [
                'id' => '99',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('99', $dto->id);
        $this->assertEquals('Cash deposit successful', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentCashDepositResponseDTO::fromApiResponse([
            'agentCashDepositRes' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9001',
                        'level' => '2',
                        'message' => 'Transaction cannot be processed due to insufficient balance.',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
        $this->assertEquals('9001', $dto->errorCode);
        $this->assertStringContainsString('insufficient balance', $dto->message);
        $this->assertEquals('9001', $dto->errors[0]['code']);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentCashDepositResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
