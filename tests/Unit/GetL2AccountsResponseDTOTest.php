<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs\GetL2AccountsResponseDTO;

class GetL2AccountsResponseDTOTest extends TestCase
{
    /**
     * Test fromArray with complete successful response data.
     */
    public function test_from_array_with_complete_response(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'ResponseDateTime' => '2024111045235146',
                'Rrn' => '229830310784',
                'HashData' => 'b7e7d7332f7866d1d1509b6b9cb784384ad19d7588c9e1522ceffcd630ca72b7',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Transaction limits up to PKR 1 million and international payments.',
                        'details' => [
                            [
                                'title' => 'Features and benifits',
                                'data' => [
                                    'Unlimite account limits',
                                    'Currency PK',
                                    'International transactions',
                                ],
                            ],
                            [
                                'title' => 'What you will need',
                                'data' => [
                                    'Your valid CNIC - you\'ll need to scan it',
                                    'Your sim registered in your name',
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => '2',
                        'accountId' => '1002',
                        'accountName' => 'ULTRA SIGNATURE',
                        'description' => 'Full fledged bank account with enhanced limits.',
                        'details' => [],
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertInstanceOf(GetL2AccountsResponseDTO::class, $dto);
        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertEquals('229830310784', $dto->rrn);
        $this->assertEquals('2024111045235146', $dto->responseDateTime);
        $this->assertEquals('b7e7d7332f7866d1d1509b6b9cb784384ad19d7588c9e1522ceffcd630ca72b7', $dto->hashData);
        $this->assertIsArray($dto->l2Accounts);
        $this->assertCount(2, $dto->l2Accounts);
        $this->assertEquals($responseData, $dto->originalResponse);
    }

    /**
     * Test fromArray parses L2 account details correctly.
     */
    public function test_from_array_parses_l2_accounts_correctly(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '229830310784',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Premium account',
                        'details' => [
                            [
                                'title' => 'Features',
                                'data' => ['Feature 1', 'Feature 2', 'Feature 3'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertCount(1, $dto->l2Accounts);
        
        $account = $dto->l2Accounts[0];
        $this->assertEquals('1', $account['id']);
        $this->assertEquals('1001', $account['accountId']);
        $this->assertEquals('ULTRA', $account['accountName']);
        $this->assertEquals('Premium account', $account['description']);
        $this->assertIsArray($account['details']);
        $this->assertCount(1, $account['details']);
        
        $detail = $account['details'][0];
        $this->assertEquals('Features', $detail['title']);
        $this->assertIsArray($detail['data']);
        $this->assertCount(3, $detail['data']);
        $this->assertEquals('Feature 1', $detail['data'][0]);
    }

    /**
     * Test fromArray with multiple account details.
     */
    public function test_from_array_with_multiple_account_details(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Premium',
                        'details' => [
                            [
                                'title' => 'Features',
                                'data' => ['F1', 'F2'],
                            ],
                            [
                                'title' => 'Requirements',
                                'data' => ['R1', 'R2', 'R3'],
                            ],
                            [
                                'title' => 'Benefits',
                                'data' => ['B1'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $account = $dto->l2Accounts[0];
        $this->assertCount(3, $account['details']);
        $this->assertEquals('Features', $account['details'][0]['title']);
        $this->assertEquals('Requirements', $account['details'][1]['title']);
        $this->assertEquals('Benefits', $account['details'][2]['title']);
        $this->assertCount(2, $account['details'][0]['data']);
        $this->assertCount(3, $account['details'][1]['data']);
        $this->assertCount(1, $account['details'][2]['data']);
    }

    /**
     * Test fromArray with minimal response (no accounts).
     */
    public function test_from_array_with_minimal_response(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
        $this->assertEquals('123456789012', $dto->rrn);
        $this->assertNull($dto->responseDateTime);
        $this->assertNull($dto->hashData);
        $this->assertIsArray($dto->l2Accounts);
        $this->assertEmpty($dto->l2Accounts);
    }

    /**
     * Test fromArray with error response.
     */
    public function test_from_array_with_error_response(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '01',
                'ResponseDescription' => 'Invalid Request',
                'Rrn' => '123456789012',
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertFalse($dto->success);
        $this->assertEquals('01', $dto->responseCode);
        $this->assertEquals('Invalid Request', $dto->message);
    }

    /**
     * Test success property is true only for response code '00'.
     */
    public function test_success_property_is_true_only_for_code_00(): void
    {
        $successData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
            ],
        ];

        $errorData = [
            'level2AccountsRes' => [
                'ResponseCode' => '99',
                'ResponseDescription' => 'Error',
                'Rrn' => '123456789012',
            ],
        ];

        $successDto = GetL2AccountsResponseDTO::fromArray($successData);
        $errorDto = GetL2AccountsResponseDTO::fromArray($errorData);

        $this->assertTrue($successDto->success);
        $this->assertFalse($errorDto->success);
    }

    /**
     * Test fromArray handles missing L2Accounts field gracefully.
     */
    public function test_from_array_handles_missing_l2_accounts_gracefully(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                // L2Accounts field is missing
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertIsArray($dto->l2Accounts);
        $this->assertEmpty($dto->l2Accounts);
    }

    /**
     * Test fromArray handles accounts without details field.
     */
    public function test_from_array_handles_accounts_without_details(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Premium',
                        // details field is missing
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertCount(1, $dto->l2Accounts);
        $account = $dto->l2Accounts[0];
        $this->assertArrayHasKey('details', $account);
        $this->assertIsArray($account['details']);
        $this->assertEmpty($account['details']);
    }

    /**
     * Test fromArray handles missing optional account fields.
     */
    public function test_from_array_handles_missing_optional_account_fields(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'L2Accounts' => [
                    [
                        // All fields missing except one
                        'accountName' => 'TEST',
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $account = $dto->l2Accounts[0];
        $this->assertNull($account['id']);
        $this->assertNull($account['accountId']);
        $this->assertEquals('TEST', $account['accountName']);
        $this->assertNull($account['description']);
    }

    /**
     * Test fromArray handles empty details array.
     */
    public function test_from_array_handles_empty_details_array(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Premium',
                        'details' => [],
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $account = $dto->l2Accounts[0];
        $this->assertIsArray($account['details']);
        $this->assertEmpty($account['details']);
    }

    /**
     * Test fromArray handles detail without data field.
     */
    public function test_from_array_handles_detail_without_data_field(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'L2Accounts' => [
                    [
                        'id' => '1',
                        'accountId' => '1001',
                        'accountName' => 'ULTRA',
                        'description' => 'Premium',
                        'details' => [
                            [
                                'title' => 'Features',
                                // data field missing
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $detail = $dto->l2Accounts[0]['details'][0];
        $this->assertEquals('Features', $detail['title']);
        $this->assertIsArray($detail['data']);
        $this->assertEmpty($detail['data']);
    }

    /**
     * Test original response is preserved.
     */
    public function test_original_response_is_preserved(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'CustomField' => 'CustomValue',
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertEquals($responseData, $dto->originalResponse);
        $this->assertArrayHasKey('level2AccountsRes', $dto->originalResponse);
        $this->assertEquals('CustomValue', $dto->originalResponse['level2AccountsRes']['CustomField']);
    }

    /**
     * Test fromArray with empty L2Accounts array.
     */
    public function test_from_array_with_empty_l2_accounts_array(): void
    {
        $responseData = [
            'level2AccountsRes' => [
                'ResponseCode' => '00',
                'ResponseDescription' => 'Success',
                'Rrn' => '123456789012',
                'L2Accounts' => [],
            ],
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertIsArray($dto->l2Accounts);
        $this->assertEmpty($dto->l2Accounts);
    }

    /**
     * Test fromArray handles response without level2AccountsRes wrapper.
     */
    public function test_from_array_handles_response_without_wrapper(): void
    {
        $responseData = [
            'ResponseCode' => '00',
            'ResponseDescription' => 'Success',
            'Rrn' => '123456789012',
        ];

        $dto = GetL2AccountsResponseDTO::fromArray($responseData);

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Success', $dto->message);
    }

    /**
     * Test manual DTO construction.
     */
    public function test_manual_dto_construction(): void
    {
        $dto = new GetL2AccountsResponseDTO(
            success: true,
            responseCode: '00',
            message: 'Test Success',
            rrn: '123456789012',
            responseDateTime: '2024111045235146',
            l2Accounts: [
                [
                    'id' => '1',
                    'accountId' => '1001',
                    'accountName' => 'TEST',
                    'description' => 'Test Account',
                    'details' => [],
                ],
            ],
            hashData: 'test_hash',
            originalResponse: ['test' => 'data']
        );

        $this->assertTrue($dto->success);
        $this->assertEquals('00', $dto->responseCode);
        $this->assertEquals('Test Success', $dto->message);
        $this->assertEquals('123456789012', $dto->rrn);
        $this->assertEquals('2024111045235146', $dto->responseDateTime);
        $this->assertCount(1, $dto->l2Accounts);
        $this->assertEquals('test_hash', $dto->hashData);
        $this->assertEquals(['test' => 'data'], $dto->originalResponse);
    }
}
