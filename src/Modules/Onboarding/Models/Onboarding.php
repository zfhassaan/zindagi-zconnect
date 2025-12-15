<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Modules\Onboarding\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Onboarding extends Model
{
    use HasFactory;

    protected $table = 'zindagi_zconnect_onboardings';

    protected $fillable = [
        'reference_id',
        'cnic',
        'full_name',
        'mobile_number',
        'email',
        'status',
        'request_data',
        'response_data',
        'verification_data',
        'completion_data',
        'completed_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'verification_data' => 'array',
        'completion_data' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

