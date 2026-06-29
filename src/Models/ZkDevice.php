<?php

namespace Rithy\ZktecoAdms\Models;

use Illuminate\Database\Eloquent\Model;

class ZkDevice extends Model
{
    protected $table = 'zk_devices';

    protected $casts = [
        'last_seen_at' => 'datetime',
        'online' => 'boolean',
        'last_payload' => 'array',
    ];

    protected $fillable = [
        'sn',
        'name',
        'ip',
        'last_seen_at',
        'online',
        'last_payload',
    ];
}
