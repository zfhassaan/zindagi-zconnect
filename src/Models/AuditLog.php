<?php

declare(strict_types=1);

namespace zfhassaan\ZindagiZconnect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'zindagi_zconnect_audit_logs';

    protected $fillable = [
        'action',
        'module',
        'data',
        'user_id',
        'reference_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

