<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountVerificationRequestDTO;

class AgentAccountVerificationRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals('03337580647', $dto->customerMobile);
        $this->assertEquals('4520355284519', $dto->cnic);
        $this->assertEquals(1063857, $dto->agentId);
        $this->assertEquals(50002, $dto->pid);
    }

    public function test_to_array_uses_account_verification_agent_req_wrapper(): void
    {
        $array = $this->validDto(isUpgrade: 1)->toArray();

        $this->assertArrayHasKey('accountVerificationAgentReq', $array);
        $this->assertEquals(5, $array['accountVerificationAgentReq']['DTID']);
        $this->assertEquals('03337580647', $array['accountVerificationAgentReq']['CMOB']);
        $this->assertEquals('4520355284519', $array['accountVerificationAgentReq']['CNIC']);
        $this->assertEquals(0, $array['accountVerificationAgentReq']['IS_RECEIVE_CASH']);
        $this->assertEquals(0, $array['accountVerificationAgentReq']['IS_HRA']);
        $this->assertEquals(1, $array['accountVerificationAgentReq']['IS_UPGRADE']);
        $this->assertEquals(1063857, $array['accountVerificationAgentReq']['AGENT_ID']);
        $this->assertEquals(50002, $array['accountVerificationAgentReq']['PID']);
        $this->assertEquals('', $array['accountVerificationAgentReq']['SEGMENT_ID']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentAccountVerificationRequestDTO::fromArray([
            'DTID' => 5,
            'CMOB' => '03337580647',
            'CNIC' => '4520355284519',
            'AGENT_ID' => 1063857,
            'PID' => 50002,
            'IS_UPGRADE' => 1,
        ]);

        $this->assertEquals(1063857, $dto->agentId);
        $this->assertEquals(1, $dto->isUpgrade);
    }

    public function test_validation_fails_for_invalid_cnic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be exactly 13 characters');

        new AgentAccountVerificationRequestDTO(
            dtid: 5,
            customerMobile: '03337580647',
            cnic: '123',
            agentId: 1063857,
            pid: 50002
        );
    }

    public function test_validation_fails_for_invalid_agent_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AGENT_ID must be greater than zero');

        new AgentAccountVerificationRequestDTO(
            dtid: 5,
            customerMobile: '03337580647',
            cnic: '4520355284519',
            agentId: 0,
            pid: 50002
        );
    }

    private function validDto(int $isUpgrade = 0): AgentAccountVerificationRequestDTO
    {
        return new AgentAccountVerificationRequestDTO(
            dtid: 5,
            customerMobile: '03337580647',
            cnic: '4520355284519',
            agentId: 1063857,
            pid: 50002,
            isUpgrade: $isUpgrade
        );
    }
}
