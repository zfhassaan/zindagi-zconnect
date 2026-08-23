<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentAccountOpeningUpgradeRequestDTO;

class AgentAccountOpeningUpgradeRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals('03422142169', $dto->customerMobile);
        $this->assertEquals('03463564149', $dto->agentMobile);
        $this->assertEquals('4250130646839', $dto->cnic);
        $this->assertEquals('AHSAN MEHMOOD', $dto->customerName);
        $this->assertEquals(2510763, $dto->pid);
        $this->assertEquals(2, $dto->customerAccountType);
    }

    public function test_to_array_uses_account_opening_agent_l0_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('accountOpeningAgentL0Req', $array);
        $req = $array['accountOpeningAgentL0Req'];
        $this->assertEquals(5, $req['DTID']);
        $this->assertEquals(1, $req['ENCT']);
        $this->assertEquals('03422142169', $req['CMOB']);
        $this->assertEquals('6tBH5Et3C3b9p7Xzr1YVIQ==', $req['PIN']);
        $this->assertEquals('4250130646839', $req['CNIC']);
        $this->assertEquals('AHSAN MEHMOOD', $req['CNAME']);
        $this->assertEquals('03463564149', $req['AMOB']);
        $this->assertEquals(2510763, $req['PID']);
        $this->assertEquals(2, $req['CUST_ACC_TYPE']);
        $this->assertEquals('Telenor', $req['CUST_MOB_NETWORK']);
        $this->assertEquals(1, $req['IS_BVS_ACCOUNT']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentAccountOpeningUpgradeRequestDTO::fromArray([
            'DTID' => 5,
            'PIN' => 'pin-value',
            'CMOB' => '03422142169',
            'CNIC' => '4250130646839',
            'BIRTH_PLACE' => 'KARACHI',
            'CNAME' => 'AHSAN MEHMOOD',
            'MOTHER_MAIDEN' => 'SHEHNAZ PARVEEN',
            'CDOB' => '1992-06-23',
            'CNIC_EXP' => '2022-12-31',
            'PRESENT_ADDR' => 'PRESENT',
            'PERMANENT_ADDR' => 'PERMANENT',
            'ACTITLE' => 'AHSAN MEHMOOD',
            'GENDER' => 'male',
            'AMOB' => '03463564149',
            'PID' => 2510763,
            'CUST_MOB_NETWORK' => 'Telenor',
            'CUST_ACC_TYPE' => 2,
        ]);

        $this->assertEquals('pin-value', $dto->pin);
        $this->assertEquals('AHSAN MEHMOOD', $dto->customerName);
        $this->assertEquals(2510763, $dto->pid);
    }

    public function test_validation_fails_for_empty_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN cannot be empty');

        $dto = $this->validDto();
        new AgentAccountOpeningUpgradeRequestDTO(
            dtid: $dto->dtid,
            pin: '',
            customerMobile: $dto->customerMobile,
            cnic: $dto->cnic,
            birthPlace: $dto->birthPlace,
            customerName: $dto->customerName,
            motherMaiden: $dto->motherMaiden,
            dateOfBirth: $dto->dateOfBirth,
            cnicExpiry: $dto->cnicExpiry,
            presentAddress: $dto->presentAddress,
            permanentAddress: $dto->permanentAddress,
            accountTitle: $dto->accountTitle,
            gender: $dto->gender,
            agentMobile: $dto->agentMobile,
            pid: $dto->pid,
            customerMobileNetwork: $dto->customerMobileNetwork,
            customerAccountType: $dto->customerAccountType
        );
    }

    public function test_validation_fails_for_invalid_cnic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNIC must be exactly 13 characters');

        $dto = $this->validDto();
        new AgentAccountOpeningUpgradeRequestDTO(
            dtid: $dto->dtid,
            pin: $dto->pin,
            customerMobile: $dto->customerMobile,
            cnic: '123',
            birthPlace: $dto->birthPlace,
            customerName: $dto->customerName,
            motherMaiden: $dto->motherMaiden,
            dateOfBirth: $dto->dateOfBirth,
            cnicExpiry: $dto->cnicExpiry,
            presentAddress: $dto->presentAddress,
            permanentAddress: $dto->permanentAddress,
            accountTitle: $dto->accountTitle,
            gender: $dto->gender,
            agentMobile: $dto->agentMobile,
            pid: $dto->pid,
            customerMobileNetwork: $dto->customerMobileNetwork,
            customerAccountType: $dto->customerAccountType
        );
    }

    private function validDto(): AgentAccountOpeningUpgradeRequestDTO
    {
        return new AgentAccountOpeningUpgradeRequestDTO(
            dtid: 5,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03422142169',
            cnic: '4250130646839',
            birthPlace: 'KARACHI SHARKI KARACHI SHARKI',
            customerName: 'AHSAN MEHMOOD',
            motherMaiden: 'SHEHNAZ PARVEEN',
            dateOfBirth: '1992-06-23',
            cnicExpiry: '2022-12-31',
            presentAddress: 'HOUSE NUMBER R-83 MOHALA PAK KOUSAR TOWN MALIR TOUSEE KARACHI SHARKI',
            permanentAddress: 'HOUSE NUMBER R-83 MOHALA PAK KOUSAR TOWN MALIR TOUSEE KARACHI SHARKI',
            accountTitle: 'AHSAN MEHMOOD',
            gender: 'male',
            agentMobile: '03463564149',
            pid: 2510763,
            customerMobileNetwork: 'Telenor',
            customerAccountType: 2
        );
    }
}
