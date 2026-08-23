<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentBillPaymentRequestDTO;

class AgentBillPaymentRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(2510765, $dto->pid);
        $this->assertEquals('03323331679', $dto->customerMobile);
        $this->assertEquals('03463564149', $dto->agentMobile);
        $this->assertEquals(200, $dto->transactionAmount);
        $this->assertEquals(1, $dto->encryptionType);
    }

    public function test_to_array_uses_agent_bill_payment_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentBillPaymentReq', $array);
        $this->assertEquals(5, $array['agentBillPaymentReq']['DTID']);
        $this->assertEquals(2510765, $array['agentBillPaymentReq']['PID']);
        $this->assertEquals('6tBH5Et3C3b9p7Xzr1YVIQ==', $array['agentBillPaymentReq']['PIN']);
        $this->assertEquals(1, $array['agentBillPaymentReq']['ENCT']);
        $this->assertEquals('03323331679', $array['agentBillPaymentReq']['CMOB']);
        $this->assertEquals('03463564149', $array['agentBillPaymentReq']['AMOB']);
        $this->assertEquals('03463564149', $array['agentBillPaymentReq']['CONSUMER']);
        $this->assertEquals(1, $array['agentBillPaymentReq']['PMTTYPE']);
        $this->assertEquals(50050, $array['agentBillPaymentReq']['BAID']);
        $this->assertEquals(200, $array['agentBillPaymentReq']['TXAM']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentBillPaymentRequestDTO::fromArray([
            'DTID' => 5,
            'PID' => 2510765,
            'PIN' => 'pin-value',
            'ENCT' => 1,
            'CMOB' => '03323331679',
            'AMOB' => '03463564149',
            'CONSUMER' => '03463564149',
            'PMTTYPE' => 1,
            'BAID' => 50050,
            'TXAM' => 200,
        ]);

        $this->assertEquals('pin-value', $dto->pin);
        $this->assertEquals(200, $dto->transactionAmount);
    }

    public function test_validation_fails_for_empty_pin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PIN cannot be empty');

        new AgentBillPaymentRequestDTO(
            dtid: 5,
            pid: 2510765,
            pin: '',
            customerMobile: '03323331679',
            agentMobile: '03463564149',
            consumer: '03463564149',
            paymentType: 1,
            billAccountId: 50050,
            transactionAmount: 200
        );
    }

    public function test_validation_fails_for_zero_transaction_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction amount must be greater than zero');

        new AgentBillPaymentRequestDTO(
            dtid: 5,
            pid: 2510765,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03323331679',
            agentMobile: '03463564149',
            consumer: '03463564149',
            paymentType: 1,
            billAccountId: 50050,
            transactionAmount: 0
        );
    }

    private function validDto(): AgentBillPaymentRequestDTO
    {
        return new AgentBillPaymentRequestDTO(
            dtid: 5,
            pid: 2510765,
            pin: '6tBH5Et3C3b9p7Xzr1YVIQ==',
            customerMobile: '03323331679',
            agentMobile: '03463564149',
            consumer: '03463564149',
            paymentType: 1,
            billAccountId: 50050,
            transactionAmount: 200
        );
    }
}
