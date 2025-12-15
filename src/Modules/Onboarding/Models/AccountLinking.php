<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLinking extends Model
{
    protected $table = 'zindagi_zconnect_account_linkings';

    protected $fillable = [
        'trace_no',
        'cnic',
        'mobile_no',
        'merchant_type',
        'request_data',
        'response_data',
        'response_code',
        'account_title',
        'account_type',
        'otp_pin',
        'success',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'success' => 'boolean',
    ];
}

