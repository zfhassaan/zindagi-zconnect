<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class UpgradeMinorAccountResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $responseDescription,
        public ?string $rrn = null,
        public ?string $hashData = null,
        public ?string $message = null,
        public ?string $errorCode = null,
        public array $originalResponse = []
    ) {
        $this->message = $message ?? $this->responseDescription;
    }

    public static function fromArray(array $data): self
    {
        return self::fromApiResponse($data);
    }

    public static function fromApiResponse(array $response): self
    {
        $hasWrapper = isset($response['upgradeMinorAccountRes']) && is_array($response['upgradeMinorAccountRes']);
        $isGatewayError = isset($response['messages']) || isset($response['errorcode']);

        if ($isGatewayError && ! $hasWrapper) {
            $errorCode = isset($response['errorcode']) ? (string) $response['errorcode'] : null;
            $message = is_string($response['messages'] ?? null)
                ? $response['messages']
                : 'Unknown error';

            return new self(
                success: false,
                responseCode: $errorCode ?? '',
                responseDescription: $message,
                message: $message,
                errorCode: $errorCode,
                originalResponse: $response
            );
        }

        $data = $hasWrapper ? $response['upgradeMinorAccountRes'] : $response;
        $responseCode = (string) ($data['ResponseCode'] ?? $data['responseCode'] ?? '');
        $responseDescription = (string) ($data['ResponseDescription'] ?? $data['responseDescription'] ?? '');

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            rrn: $data['Rrn'] ?? $data['rrn'] ?? $data['RRN'] ?? null,
            hashData: $data['HashData'] ?? $data['hashData'] ?? null,
            message: $responseDescription,
            errorCode: $responseCode !== '00' && $responseCode !== '' ? $responseCode : null,
            originalResponse: $response
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'response_code' => $this->responseCode,
            'response_description' => $this->responseDescription,
            'rrn' => $this->rrn,
            'hash_data' => $this->hashData,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
