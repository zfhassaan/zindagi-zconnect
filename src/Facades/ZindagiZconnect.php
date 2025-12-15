<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \zfhassaan\ZindagiZconnect\Modules\Onboarding\Services\Contracts\OnboardingServiceInterface onboarding()
 * @method static \zfhassaan\ZindagiZconnect\Services\Contracts\AuthenticationServiceInterface auth()
 * @method static \zfhassaan\ZindagiZconnect\Services\Contracts\HttpClientInterface http()
 * @method static \zfhassaan\ZindagiZconnect\Services\Contracts\LoggingServiceInterface logger()
 * @method static \zfhassaan\ZindagiZconnect\Services\Contracts\AuditServiceInterface audit()
 *
 * @see \zfhassaan\ZindagiZconnect\ZindagiZconnect
 */
class ZindagiZconnect extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'zindagi-zconnect';
    }
}

