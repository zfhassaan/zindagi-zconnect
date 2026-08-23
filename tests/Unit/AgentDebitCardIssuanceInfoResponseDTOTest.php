<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceInfoResponseDTO;

class AgentDebitCardIssuanceInfoResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentDebitCardIssuanceInfoResponseDTO::fromApiResponse([
            'agentDebitCardIssuanceInfoRes' => [
                'id' => '36',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('36', $dto->id);
        $this->assertEquals('Debit card issuance info retrieved successfully', $dto->message);
    }

    public function test_from_api_response_portal_typo_wrapper(): void
    {
        $dto = AgentDebitCardIssuanceInfoResponseDTO::fromApiResponse([
            'agentDebitCardIssuanceInfoReq' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9001',
                        'level' => '2',
                        'message' => 'Upgrade Customer Account From L0 to L1.',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('9001', $dto->errorCode);
        $this->assertStringContainsString('Upgrade Customer Account', $dto->message);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentDebitCardIssuanceInfoResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
