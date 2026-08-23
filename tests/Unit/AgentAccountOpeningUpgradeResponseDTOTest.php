<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountOpeningUpgradeResponseDTO;

class AgentAccountOpeningUpgradeResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentAccountOpeningUpgradeResponseDTO::fromApiResponse([
            'accountOpeningAgentL0Res' => [
                'id' => '99',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('99', $dto->id);
        $this->assertEquals('Agent account opening successful', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentAccountOpeningUpgradeResponseDTO::fromApiResponse([
            'accountOpeningAgentL0Res' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9096',
                        'level' => '2',
                        'message' => 'CNIC is expired, please use valid CINC to open account.',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
        $this->assertEquals('9096', $dto->errorCode);
        $this->assertStringContainsString('CNIC is expired', $dto->message);
        $this->assertEquals('9096', $dto->errors[0]['code']);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentAccountOpeningUpgradeResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
