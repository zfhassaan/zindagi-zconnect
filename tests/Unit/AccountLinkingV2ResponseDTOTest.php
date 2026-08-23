<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountLinkingV2ResponseDTO;

class AccountLinkingV2ResponseDTOTest extends TestCase
{
    public function test_from_api_response_success(): void
    {
        $dto = AccountLinkingV2ResponseDTO::fromApiResponse([
            'LinkAccountResponse' => [
                'MerchantType' => '0088',
                'TraceNo' => '000001',
                'CompanyName' => 'NOVA',
                'DateTime' => '20210105201527',
                'AccountTitle' => 'JOHN DOE',
                'AccountType' => 'Level0',
                'ResponseCode' => '00',
                'ResponseDetails' => ['Successful'],
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('JOHN DOE', $dto->accountTitle);
        $this->assertEquals('Successful', $dto->message);
    }

    public function test_from_api_response_failure(): void
    {
        $dto = AccountLinkingV2ResponseDTO::fromApiResponse([
            'LinkAccountResponse' => [
                'ResponseCode' => '01',
                'ResponseDetails' => ['Invalid OTP'],
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('Invalid OTP', $dto->message);
    }

    public function test_from_api_response_gateway_error(): void
    {
        $dto = AccountLinkingV2ResponseDTO::fromApiResponse([
            'messages' => 'Bad Request - Invalid Access Token',
            'errorcode' => '4001',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('4001', $dto->errorCode);
        $this->assertEquals('Bad Request - Invalid Access Token', $dto->message);
    }
}
