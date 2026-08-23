<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AgentBillPaymentInquiryRequestDTO;

class AgentBillPaymentInquiryRequestDTOTest extends TestCase
{
    public function test_successful_dto_creation(): void
    {
        $dto = $this->validDto();

        $this->assertEquals(5, $dto->dtid);
        $this->assertEquals(2510765, $dto->pid);
        $this->assertEquals('03463564149', $dto->agentMobile);
        $this->assertEquals('03323331679', $dto->customerMobile);
        $this->assertEquals('03463564149', $dto->consumer);
        $this->assertEquals(1, $dto->paymentType);
        $this->assertEquals(50050, $dto->billAccountId);
    }

    public function test_to_array_uses_agent_bill_payment_inquiry_req_wrapper(): void
    {
        $array = $this->validDto()->toArray();

        $this->assertArrayHasKey('agentBillPaymentInquiryReq', $array);
        $this->assertEquals(5, $array['agentBillPaymentInquiryReq']['DTID']);
        $this->assertEquals(2510765, $array['agentBillPaymentInquiryReq']['PID']);
        $this->assertEquals('03463564149', $array['agentBillPaymentInquiryReq']['AMOB']);
        $this->assertEquals('03323331679', $array['agentBillPaymentInquiryReq']['CMOB']);
        $this->assertEquals('03463564149', $array['agentBillPaymentInquiryReq']['CONSUMER']);
        $this->assertEquals(1, $array['agentBillPaymentInquiryReq']['PMTTYPE']);
        $this->assertEquals(50050, $array['agentBillPaymentInquiryReq']['BAID']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AgentBillPaymentInquiryRequestDTO::fromArray([
            'DTID' => 5,
            'PID' => 2510765,
            'AMOB' => '03463564149',
            'CMOB' => '03323331679',
            'CONSUMER' => '03463564149',
            'PMTTYPE' => 1,
            'BAID' => 50050,
        ]);

        $this->assertEquals('03463564149', $dto->consumer);
        $this->assertEquals(50050, $dto->billAccountId);
    }

    public function test_validation_fails_for_empty_consumer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Consumer cannot be empty');

        new AgentBillPaymentInquiryRequestDTO(
            dtid: 5,
            pid: 2510765,
            agentMobile: '03463564149',
            customerMobile: '03323331679',
            consumer: '',
            paymentType: 1,
            billAccountId: 50050
        );
    }

    public function test_validation_fails_for_invalid_bill_account_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bill account ID must be greater than zero');

        new AgentBillPaymentInquiryRequestDTO(
            dtid: 5,
            pid: 2510765,
            agentMobile: '03463564149',
            customerMobile: '03323331679',
            consumer: '03463564149',
            paymentType: 1,
            billAccountId: 0
        );
    }

    private function validDto(): AgentBillPaymentInquiryRequestDTO
    {
        return new AgentBillPaymentInquiryRequestDTO(
            dtid: 5,
            pid: 2510765,
            agentMobile: '03463564149',
            customerMobile: '03323331679',
            consumer: '03463564149',
            paymentType: 1,
            billAccountId: 50050
        );
    }
}
