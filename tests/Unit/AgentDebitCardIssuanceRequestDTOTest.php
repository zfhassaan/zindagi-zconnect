<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentDebitCardIssuanceRequestDTO;

class AgentDebitCardIssuanceRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals('4520355284519', $dto->cnic);
        $this->assertEquals('03337580647', $dto->customerMobile);
        $this->assertEquals('ABDUL AZIZ', $dto->cardDescription);
        $this->assertEquals('karachi pakistan india', $dto->mailingAddress);
        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(1, $dto->appId);
    }

    public function test_to_array_uses_agent_debit_card_issuance_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentDebitCardIssuanceReq', $array);
        $this->assertEquals('4520355284519', $array['agentDebitCardIssuanceReq']['CNIC']);
        $this->assertEquals('03337580647', $array['agentDebitCardIssuanceReq']['CMOB']);
        $this->assertEquals('ABDUL AZIZ', $array['agentDebitCardIssuanceReq']['CARD_DESCRIPTION']);
        $this->assertEquals('karachi pakistan india', $array['agentDebitCardIssuanceReq']['MAILING_ADDRESS']);
        $this->assertEquals(1, $array['agentDebitCardIssuanceReq']['APPID']);
        $this->assertEquals(5, $array['agentDebitCardIssuanceReq']['DTID']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentDebitCardIssuanceRequestDTO::fromArray([
            'CNIC' => '4520355284519',
            'CMOB' => '03337580647',
            'CARD_DESCRIPTION' => 'ABDUL AZIZ',
            'MAILING_ADDRESS' => 'karachi pakistan india',
            'APPID' => 1,
            'DTID' => 5,
        ]);

        $this->assertEquals('ABDUL AZIZ', $dto->cardDescription);
        $this->assertEquals('karachi pakistan india', $dto->mailingAddress);
    }

    public function test_validation_fails_for_empty_card_description(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Card description cannot be empty');

        new AgentDebitCardIssuanceRequestDTO(
            cnic: '4520355284519',
            customerMobile: '03337580647',
            cardDescription: '',
            mailingAddress: 'karachi pakistan india',
            dtid: 5
        );
    }

    public function test_validation_fails_for_empty_mailing_address(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mailing address cannot be empty');

        new AgentDebitCardIssuanceRequestDTO(
            cnic: '4520355284519',
            customerMobile: '03337580647',
            cardDescription: 'ABDUL AZIZ',
            mailingAddress: '',
            dtid: 5
        );
    }

    private function validDto(): AgentDebitCardIssuanceRequestDTO
    {
        return new AgentDebitCardIssuanceRequestDTO(
            cnic: '4520355284519',
            customerMobile: '03337580647',
            cardDescription: 'ABDUL AZIZ',
            mailingAddress: 'karachi pakistan india',
            dtid: 5
        );
    }
}
