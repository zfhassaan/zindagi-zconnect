<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class MinorAccountOpeningResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $responseDescription,
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
        $hasWrapper = isset($response['minorAccountOpeningRes']) && is_array($response['minorAccountOpeningRes']);
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

        $data = $hasWrapper ? $response['minorAccountOpeningRes'] : $response;
        $responseCode = (string) ($data['responseCode'] ?? $data['ResponseCode'] ?? '');
        $responseDescription = (string) ($data['responseDescription'] ?? $data['ResponseDescription'] ?? '');

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            hashData: $data['hashData'] ?? $data['HashData'] ?? null,
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
            'hash_data' => $this->hashData,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
