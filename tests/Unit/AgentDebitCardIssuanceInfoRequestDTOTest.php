<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceInfoRequestDTO;

class AgentDebitCardIssuanceInfoRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals('4520355284519', $dto->cnic);
        $this->assertEquals('03337580647', $dto->customerMobile);
        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(1, $dto->appId);
    }

    public function test_to_array_uses_agent_debit_card_issuance_info_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentDebitCardIssuanceInfoReq', $array);
        $this->assertEquals('4520355284519', $array['agentDebitCardIssuanceInfoReq']['CNIC']);
        $this->assertEquals('03337580647', $array['agentDebitCardIssuanceInfoReq']['CMOB']);
        $this->assertEquals(1, $array['agentDebitCardIssuanceInfoReq']['APPID']);
        $this->assertEquals(5, $array['agentDebitCardIssuanceInfoReq']['DTID']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentDebitCardIssuanceInfoRequestDTO::fromArray([
            'CNIC' => '4520355284519',
            'CMOB' => '03337580647',
            'APPID' => 1,
            'DTID' => 5,
        ]);

        $this->assertEquals('4520355284519', $dto->cnic);
        $this->assertEquals('03337580647', $dto->customerMobile);
    }

    public function test_validation_fails_for_invalid_cnic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be exactly 13 characters');

        new AgentDebitCardIssuanceInfoRequestDTO(
            cnic: '123',
            customerMobile: '03337580647',
            dtid: 5
        );
    }

    public function test_validation_fails_for_invalid_mobile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer mobile must be exactly 11 characters');

        new AgentDebitCardIssuanceInfoRequestDTO(
            cnic: '4520355284519',
            customerMobile: '033',
            dtid: 5
        );
    }

    private function validDto(): AgentDebitCardIssuanceInfoRequestDTO
    {
        return new AgentDebitCardIssuanceInfoRequestDTO(
            cnic: '4520355284519',
            customerMobile: '03337580647',
            dtid: 5
        );
    }
}
