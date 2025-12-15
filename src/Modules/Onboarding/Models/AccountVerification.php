<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountVerification extends Model
{
    use HasFactory;

    protected $table = 'zindagi_zconnect_account_verifications';

    protected $fillable = [
        'trace_no',
        'cnic',
        'mobile_no',
        'merchant_type',
        'request_data',
        'response_data',
        'response_code',
        'account_status',
        'account_title',
        'account_type',
        'is_pin_set',
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

