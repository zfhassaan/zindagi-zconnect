<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Models;

use Illuminate\Database\Eloquent\Model;

class AccountOpening extends Model
{
    protected $table = 'zindagi_zconnect_account_openings';

    protected $fillable = [
        'trace_no',
        'cnic',
        'mobile_no',
        'email_id',
        'cnic_issuance_date',
        'mobile_network',
        'merchant_type',
        'request_data',
        'response_data',
        'response_code',
        'success',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'success' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

