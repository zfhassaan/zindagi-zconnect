<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentCashWithdrawalInquiryResponseDTO;

class AgentCashWithdrawalInquiryResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentCashWithdrawalInquiryResponseDTO::fromApiResponse([
            'agentCashdWithDrawlIinquiryRes' => [
                'id' => '88',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('88', $dto->id);
        $this->assertEquals('Cash withdrawal inquiry successful', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentCashWithdrawalInquiryResponseDTO::fromApiResponse([
            'agentCashdWithDrawlIinquiryRes' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '8076',
                        'level' => '2',
                        'message' => 'Would you like to register the given Mobile Number as a branchless banking customer?',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
        $this->assertEquals('8076', $dto->errorCode);
        $this->assertStringContainsString('register the given Mobile Number', $dto->message);
        $this->assertEquals('8076', $dto->errors[0]['code']);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentCashWithdrawalInquiryResponseDTO::fromApiResponse([
            'messages' => 'Record Not Found',
            'errorcode' => '4005',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4005', $dto->errorCode);
        $this->assertEquals('Record Not Found', $dto->message);
    }
}
