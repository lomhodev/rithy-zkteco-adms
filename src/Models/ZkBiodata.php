<?php

namespace Rithy\ZktecoAdms\Models;

use Illuminate\Database\Eloquent\Model;

class ZkBiodata extends Model
{
    protected $table = 'zk_biodatas';

    protected $casts = [
        'biometric_type' => 'integer',
        'template_no' => 'integer',
        'template_index' => 'integer',
        'valid' => 'boolean',
        'duress' => 'boolean',
        'major_version' => 'integer',
        'minor_version' => 'integer',
        'format' => 'integer',
    ];

    protected $fillable = [
        'device_sn',
        'pin',
        'biometric_type',
        'template_no',
        'template_index',
        'valid',
        'duress',
        'major_version',
        'minor_version',
        'format',
        'template',
        'raw_data',
    ];
}
