<?php

namespace Rithy\ZktecoAdms\Models;

use Illuminate\Database\Eloquent\Model;

class ZkCommand extends Model
{
    protected $table = 'zk_commands';

    protected $casts = [
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $fillable = [
        'device_sn',
        'command',
        'status',
        'response',
        'sent_at',
        'completed_at',
    ];
}
