<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\AccountStatementV2ResponseDTO;

class AccountStatementV2ResponseDTOTest extends TestCase
{
    public function test_from_api_response_with_complete_wrapper(): void
    {
        $dto = AccountStatementV2ResponseDTO::fromApiResponse([
            'AccountStatementRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'ClosingBalanceStatement' => [
                    [
                        'dateTime' => '20250116174251',
                        'mobileNumber' => '03343118436',
                        'dayEndBalance' => '5000',
                    ],
                ],
                'DigiWalletStatement' => [
                    [
                        'transactionAmount' => 100,
                        'transactionType' => 'Debit',
                        'mobileNumber' => '03343118436',
                    ],
                ],
                'HashData' => 'some-hash',
                'ResponseDateTime' => '20250116174253',
                'Rrn' => '123456789',
            ],
        ]);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Successful', $dto->responseDescription);
        $this->assertEquals('Successful', $dto->message);
        $this->assertEquals('some-hash', $dto->hashData);
        $this->assertEquals('123456789', $dto->rrn);
        $this->assertCount(1, $dto->closingBalanceStatement);
        $this->assertCount(1, $dto->digiWalletStatement);
        $this->assertEquals('5000', $dto->closingBalanceStatement[0]['dayEndBalance']);
        $this->assertNull($dto->errorCode);
    }

    public function test_from_array_handles_unwrapped_response(): void
    {
        $dto = AccountStatementV2ResponseDTO::fromArray([
            'ResponseCode' => '00',
            'ResponseDescription' => 'Successful',
            'DigiWalletStatement' => [],
        ]);

        $this->assertTrue($dto->success);
        $this->assertSame([], $dto->digiWalletStatement);
    }

    public function test_from_api_response_with_business_error_code(): void
    {
        $dto = AccountStatementV2ResponseDTO::fromApiResponse([
            'AccountStatementRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'No transactions found',
                'Rrn' => '123456789',
            ],
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('01', $dto->errorCode);
        $this->assertEquals('No transactions found', $dto->message);
    }

    public function test_from_api_response_with_gateway_error(): void
    {
        $dto = AccountStatementV2ResponseDTO::fromApiResponse([
            'messages' => 'Unauthorized',
            'errorcode' => '401',
        ]);

        $this->assertFalse($dto->success);
        $this->assertEquals('401', $dto->errorCode);
        $this->assertEquals('Unauthorized', $dto->message);
    }

    public function test_to_array_includes_mapped_fields(): void
    {
        $dto = AccountStatementV2ResponseDTO::fromApiResponse([
            'AccountStatementRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Successful',
                'Rrn' => '123456789',
            ],
        ]);

        $array = $dto->toArray();

        $this->assertTrue($array['success']);
        $this->assertEquals('00', $array['response_code']);
        $this->assertEquals('123456789', $array['rrn']);
        $this->assertIsArray($array['closing_balance_statement']);
        $this->assertIsArray($array['digi_wallet_statement']);
    }
}
