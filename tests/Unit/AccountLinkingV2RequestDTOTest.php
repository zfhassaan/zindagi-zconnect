<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingV2RequestDTO;

class AccountLinkingV2RequestDTOTest extends TestCase
{
    public function test_successful_dto_creation_with_required_fields(): void
    {
        $dto = $this->validDto();

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('03001234567', $dto->mobileNo);
        $this->assertEquals('encrypted-mpin', $dto->mPin);
        $this->assertEquals('encrypted-mpin', $dto->confirmMpin);
    }

    public function test_to_api_request_uses_link_account_request_wrapper(): void
    {
        $request = $this->validDto(otpPin: '123456')->toApiRequest();

        $this->assertArrayHasKey('LinkAccountRequest', $request);
        $this->assertEquals('1234567890123', $request['LinkAccountRequest']['Cnic']);
        $this->assertEquals('03001234567', $request['LinkAccountRequest']['MobileNo']);
        $this->assertEquals('encrypted-mpin', $request['LinkAccountRequest']['mPin']);
        $this->assertEquals('encrypted-mpin', $request['LinkAccountRequest']['confirmMpin']);
        $this->assertEquals('123456', $request['LinkAccountRequest']['OtpPin']);
    }

    public function test_from_array_supports_snake_case_and_api_keys(): void
    {
        $dto = AccountLinkingV2RequestDTO::fromArray([
            'CNIC' => '1234567890123',
            'MobileNo' => '03001234567',
            'mPin' => 'pin-a',
            'confirmMpin' => 'pin-b',
            'TraceNo' => '000001',
            'DateTime' => '20210105201527',
        ]);

        $this->assertEquals('1234567890123', $dto->cnic);
        $this->assertEquals('000001', $dto->traceNo);
        $this->assertEquals('pin-a', $dto->mPin);
        $this->assertEquals('pin-b', $dto->confirmMpin);
    }

    private function validDto(?string $otpPin = null): AccountLinkingV2RequestDTO {
        return new AccountLinkingV2RequestDTO(
            cnic: '1234567890123',
            mobileNo: '03001234567',
            mPin: 'encrypted-mpin',
            confirmMpin: 'encrypted-mpin',
            traceNo: '000001',
            dateTime: '20210105201527',
            otpPin: $otpPin
        );
    }
}
