<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountVerificationResponseDTO;

class AgentAccountVerificationResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentAccountVerificationResponseDTO::fromApiResponse([
            'accountVerificationAgentRes' => [
                'id' => '12345',
                'errors' => [],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('12345', $dto->id);
        $this->assertSame([], $dto->errors);
        $this->assertEquals('Account verified successfully', $dto->message);
    }

    public function test_from_api_response_portal_error_sample(): void
    {
        $dto = AgentAccountVerificationResponseDTO::fromApiResponse([
            'accountVerificationAgentRes' => [
                'id' => '-1',
                'errors' => [
                    [
                        'code' => '9007',
                        'level' => '3',
                        'message' => 'This session has expired, please login again.',
                        'THIRD_PARTY_TRANSACTION_ID' => '',
                        'nadraSessionId' => '',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('-1', $dto->id);
        $this->assertEquals('9007', $dto->errorCode);
        $this->assertEquals('This session has expired, please login again.', $dto->message);
        $this->assertEquals('9007', $dto->errors[0]['code']);
        $this->assertEquals('', $dto->errors[0]['third_party_transaction_id']);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentAccountVerificationResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Request Payload',
            'errorcode' => '4002',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4002', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Request Payload', $dto->message);
    }
}
