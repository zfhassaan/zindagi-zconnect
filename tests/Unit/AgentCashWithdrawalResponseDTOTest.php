<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalResponseDTO;

class AgentCashWithdrawalResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentCashWithdrawalResponseDTO::fromApiResponse([
            'agentCashdWithDrawlRes' => [
                'id' => '99',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('99', $dto->id);
        $this->assertEquals('Cash withdrawal successful', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentCashWithdrawalResponseDTO::fromApiResponse([
            'agentCashdWithDrawlRes' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9000',
                        'level' => '3',
                        'message' => "Customer not found.\nCustomer not found.",
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
        $this->assertEquals('9000', $dto->errorCode);
        $this->assertStringContainsString('Customer not found', $dto->message);
        $this->assertEquals('9000', $dto->errors[0]['code']);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentCashWithdrawalResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
