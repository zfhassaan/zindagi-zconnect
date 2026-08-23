<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AccountUpgradeV2ResponseDTO
{
    public function __construct(
        public bool $success,
        public string $responseCode,
        public string $responseDescription,
        public ?string $token = null,
        public ?bool $coolingOffStatus = null,
        public ?string $coolingOffTime = null,
        public ?string $coolingOffText = null,
        public ?string $coolingOffTimeLeft = null,
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
        $hasWrapper = isset($response['UpgradeAcc']) && is_array($response['UpgradeAcc']);
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

        $payload = $hasWrapper ? $response['UpgradeAcc'] : $response;
        $responseCode = (string) ($payload['responseCode'] ?? $payload['ResponseCode'] ?? '');
        $responseDescription = (string) ($payload['responseDescription'] ?? $payload['ResponseDescription'] ?? '');

        if ($responseDescription === '' && isset($payload['ResponseDetails'])) {
            $responseDescription = is_array($payload['ResponseDetails'])
                ? implode(', ', $payload['ResponseDetails'])
                : (string) $payload['ResponseDetails'];
        }

        return new self(
            success: $responseCode === '00',
            responseCode: $responseCode,
            responseDescription: $responseDescription,
            token: $payload['token'] ?? $payload['Token'] ?? null,
            coolingOffStatus: isset($payload['coolingOffStatus']) || isset($payload['CoolingOffStatus'])
                ? (bool) ($payload['coolingOffStatus'] ?? $payload['CoolingOffStatus'])
                : null,
            coolingOffTime: $payload['coolingOffTime'] ?? $payload['CoolingOffTime'] ?? null,
            coolingOffText: $payload['coolingOffText'] ?? $payload['CoolingOffText'] ?? null,
            coolingOffTimeLeft: $payload['coolingOffTimeLeft'] ?? $payload['CoolingOffTimeLeft'] ?? null,
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
            'token' => $this->token,
            'cooling_off_status' => $this->coolingOffStatus,
            'cooling_off_time' => $this->coolingOffTime,
            'cooling_off_text' => $this->coolingOffText,
            'cooling_off_time_left' => $this->coolingOffTimeLeft,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
