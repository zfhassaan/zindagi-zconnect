<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDeviceChangedResponseDTO;

class AgentDeviceChangedResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentDeviceChangedResponseDTO::fromApiResponse([
            'agentDeviceChangedRes' => [
                'id' => '99',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('99', $dto->id);
        $this->assertEquals('Device changed successfully', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentDeviceChangedResponseDTO::fromApiResponse([
            'agentDeviceChangedRes' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9028',
                        'level' => '3',
                        'message' => 'Your OTP is not valid. Please enter the correct OTP.',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('9028', $dto->errorCode);
        $this->assertStringContainsString('OTP is not valid', $dto->message);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentDeviceChangedResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
    }
}
