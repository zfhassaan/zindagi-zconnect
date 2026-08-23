<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentOtpVerificationResponseDTO;

class AgentOtpVerificationResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentOtpVerificationResponseDTO::fromApiResponse([
            'agentOtpVerificationRes' => [
                'id' => '99',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('99', $dto->id);
        $this->assertEquals('OTP verified successfully', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentOtpVerificationResponseDTO::fromApiResponse([
            'agentOtpVerificationRes' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9006',
                        'level' => '3',
                        'message' => 'Service unavailable due to technical difficulties, please try again or contact service provider.',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
        $this->assertEquals('9006', $dto->errorCode);
        $this->assertStringContainsString('Service unavailable', $dto->message);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentOtpVerificationResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
