<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AgentAccountOpeningUpgradeResponseDTO
{
    /**
     * @param array<int, array<string, mixed>> $errors
     */
    public function __construct(
        public bool $success,
        public ?string $id = null,
        public array $errors = [],
        public ?string $message = null,
        public ?string $errorCode = null,
        public array $originalResponse = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return self::fromApiResponse($data);
    }

    public static function fromApiResponse(array $response): self
    {
        $hasWrapper = isset($response['accountOpeningAgentL0Res'])
            && is_array($response['accountOpeningAgentL0Res']);
        $isGatewayError = isset($response['messages']) || isset($response['errorcode']);

        if ($isGatewayError && ! $hasWrapper) {
            $errorCode = isset($response['errorcode']) ? (string) $response['errorcode'] : null;
            $message = is_string($response['messages'] ?? null)
                ? $response['messages']
                : 'Unknown error';

            return new self(
                success: false,
                message: $message,
                errorCode: $errorCode,
                originalResponse: $response
            );
        }

        $data = $hasWrapper ? $response['accountOpeningAgentL0Res'] : $response;
        $id = isset($data['id']) ? (string) $data['id'] : null;
        $errors = self::normalizeErrors($data['errors'] ?? []);
        $firstError = $errors[0] ?? null;

        $success = $id !== null && $id !== '' && $id !== '-1' && $errors === [];

        return new self(
            success: $success,
            id: $id,
            errors: $errors,
            message: $firstError['message'] ?? ($success ? 'Agent account opening successful' : null),
            errorCode: $firstError['code'] ?? ($success ? null : $id),
            originalResponse: $response
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeErrors(mixed $errors): array
    {
        if (! is_array($errors)) {
            return [];
        }

        $normalized = [];

        foreach ($errors as $error) {
            if (! is_array($error)) {
                continue;
            }

            $normalized[] = [
                'code' => isset($error['code']) ? (string) $error['code'] : null,
                'level' => isset($error['level']) ? (string) $error['level'] : null,
                'message' => $error['message'] ?? null,
                'third_party_transaction_id' => $error['THIRD_PARTY_TRANSACTION_ID'] ?? null,
                'nadra_session_id' => $error['nadraSessionId'] ?? null,
            ];
        }

        return $normalized;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'id' => $this->id,
            'errors' => $this->errors,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
