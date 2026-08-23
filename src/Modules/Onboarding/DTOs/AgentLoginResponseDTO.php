<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\DTOs;

class AgentLoginResponseDTO
{
    public function __construct(
        public bool $success,
        public ?string $id = null,
        public ?string $agentType = null,
        public ?string $agentMobile = null,
        public ?string $senderIban = null,
        public ?string $videoLink = null,
        public ?string $userType = null,
        public ?string $lastName = null,
        public ?string $balanceFormatted = null,
        public ?string $appVersion = null,
        public ?string $agentAreaName = null,
        public ?string $balance = null,
        public ?string $agentDeviceType = null,
        public ?string $isSetMpinLater = null,
        public ?string $firstName = null,
        public ?string $imprc = null,
        public ?string $ipcr = null,
        public ?string $apul = null,
        public ?string $bvse = null,
        public ?string $isMigrated = null,
        public ?string $welcomeMessage = null,
        public ?string $cnic = null,
        public ?string $isCnicExpiryRequired = null,
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
        $hasWrapper = isset($response['loginAgentRes']) && is_array($response['loginAgentRes']);
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

        $data = $hasWrapper ? $response['loginAgentRes'] : $response;
        $id = isset($data['id']) ? (string) $data['id'] : null;
        $welcomeMessage = $data['TSTR'] ?? null;

        return new self(
            success: $id !== null && $id !== '',
            id: $id,
            agentType: isset($data['ATYPE']) ? (string) $data['ATYPE'] : null,
            agentMobile: $data['AMOB'] ?? null,
            senderIban: $data['SENDER_IBAN'] ?? null,
            videoLink: $data['VIDEOLINK'] ?? null,
            userType: isset($data['USTY']) ? (string) $data['USTY'] : null,
            lastName: $data['LNAME'] ?? null,
            balanceFormatted: $data['BALF'] ?? null,
            appVersion: $data['APPV'] ?? null,
            agentAreaName: $data['AGENT_AREA_NAME'] ?? null,
            balance: $data['BAL'] ?? null,
            agentDeviceType: isset($data['ADTYPE']) ? (string) $data['ADTYPE'] : null,
            isSetMpinLater: isset($data['IS_SET_MPIN_LATER']) ? (string) $data['IS_SET_MPIN_LATER'] : null,
            firstName: $data['FNAME'] ?? null,
            imprc: isset($data['IMPCR']) ? (string) $data['IMPCR'] : null,
            ipcr: isset($data['IPCR']) ? (string) $data['IPCR'] : null,
            apul: isset($data['APUL']) ? (string) $data['APUL'] : null,
            bvse: isset($data['BVSE']) ? (string) $data['BVSE'] : null,
            isMigrated: isset($data['IS_MIGRATED']) ? (string) $data['IS_MIGRATED'] : null,
            welcomeMessage: $welcomeMessage,
            cnic: $data['CNIC'] ?? null,
            isCnicExpiryRequired: isset($data['IS_CNIC_EXPIRY_REQUIRED']) ? (string) $data['IS_CNIC_EXPIRY_REQUIRED'] : null,
            message: $welcomeMessage,
            originalResponse: $response
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'id' => $this->id,
            'agent_type' => $this->agentType,
            'agent_mobile' => $this->agentMobile,
            'sender_iban' => $this->senderIban,
            'video_link' => $this->videoLink,
            'user_type' => $this->userType,
            'last_name' => $this->lastName,
            'balance_formatted' => $this->balanceFormatted,
            'app_version' => $this->appVersion,
            'agent_area_name' => $this->agentAreaName,
            'balance' => $this->balance,
            'agent_device_type' => $this->agentDeviceType,
            'is_set_mpin_later' => $this->isSetMpinLater,
            'first_name' => $this->firstName,
            'imprc' => $this->imprc,
            'ipcr' => $this->ipcr,
            'apul' => $this->apul,
            'bvse' => $this->bvse,
            'is_migrated' => $this->isMigrated,
            'welcome_message' => $this->welcomeMessage,
            'cnic' => $this->cnic,
            'is_cnic_expiry_required' => $this->isCnicExpiryRequired,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
