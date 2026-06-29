<?php

namespace Rithy\ZktecoAdms\Models;

use Illuminate\Database\Eloquent\Model;

class ZkUser extends Model
{
    protected $table = 'zk_users';

    protected $casts = [
        'privilege' => 'integer',
    ];

    protected $fillable = [
        'pin',
        'name',
        'privilege',
        'password',
        'card',
        'group',
        'timezone',
        'raw',
    ];
}
