<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentLoginResponseDTO;

class AgentLoginResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AgentLoginResponseDTO::fromApiResponse([
            'loginAgentRes' => [
                'id' => '33',
                'ATYPE' => '3',
                'AMOB' => '03324779796',
                'SENDER_IBAN' => null,
                'VIDEOLINK' => 'http://example.com/video.3gp',
                'USTY' => '3',
                'LNAME' => 'AMEN',
                'BALF' => '0.00',
                'APPV' => '2.0.9.9',
                'AGENT_AREA_NAME' => 'Punjab',
                'BAL' => '0.0',
                'ADTYPE' => '1',
                'IS_SET_MPIN_LATER' => null,
                'FNAME' => 'AMEN',
                'IMPCR' => '0',
                'IPCR' => '0',
                'APUL' => '1',
                'BVSE' => '0',
                'IS_MIGRATED' => '0',
                'TSTR' => 'Dear Agent! Welcome to Branchless Banking',
                'CNIC' => '3380213794351',
                'IS_CNIC_EXPIRY_REQUIRED' => '0',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('33', $dto->id);
        $this->assertEquals('03324779796', $dto->agentMobile);
        $this->assertEquals('AMEN', $dto->firstName);
        $this->assertEquals('Punjab', $dto->agentAreaName);
        $this->assertEquals('Dear Agent! Welcome to Branchless Banking', $dto->welcomeMessage);
        $this->assertEquals('3380213794351', $dto->cnic);
    }

    public function test_from_api_response_without_agent_id_is_failure(): void
    {
        $dto = AgentLoginResponseDTO::fromApiResponse([
            'loginAgentRes' => [
                'TSTR' => 'Invalid credentials',
            ],
        ]);

        $this->assertFalse($dto->success);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AgentLoginResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
