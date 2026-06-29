<?php

namespace Rithy\ZktecoAdms\Models;

use Illuminate\Database\Eloquent\Model;

class ZkAttendance extends Model
{
    protected $table = 'zk_attendances';

    protected $casts = [
        'punch_time' => 'datetime',
        'verify_type' => 'integer',
        'punch_state' => 'integer',
    ];

    protected $fillable = [
        'device_sn',
        'pin',
        'punch_time',
        'verify_type',
        'punch_state',
        'raw',
    ];
}
