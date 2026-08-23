<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceResponseDTO;

class AgentDebitCardIssuanceResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentDebitCardIssuanceResponseDTO::fromApiResponse([
            'agentDebitCardIssuanceRes' => [
                'id' => '99',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('99', $dto->id);
        $this->assertEquals('Debit card issuance successful', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentDebitCardIssuanceResponseDTO::fromApiResponse([
            'agentDebitCardIssuanceRes' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9005',
                        'level' => '2',
                        'message' => 'Your card request is already in process. Please contact Bank call center for further detail.',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
        $this->assertEquals('9005', $dto->errorCode);
        $this->assertStringContainsString('already in process', $dto->message);
        $this->assertEquals('9005', $dto->errors[0]['code']);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentDebitCardIssuanceResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
