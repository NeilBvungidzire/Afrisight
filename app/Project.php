<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model {

    use SoftDeletes;

    protected $primaryKey = 'project_code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'project_code',
        'description',
        'country_code',
        'is_live',
        'total_complete_limit',
        'enabled_via_web_app',
        'enabled_for_admin',
        'is_ready_to_run',
        'targets',
        'targets_relation',
        'configs',
    ];

    protected $casts = [
        'is_live'              => 'boolean',
        'total_complete_limit' => 'integer',
        'enabled_via_web_app'  => 'boolean',
        'enabled_for_admin'    => 'boolean',
        'is_ready_to_run'      => 'boolean',
        'targets'              => 'array',
        'targets_relation'     => 'array',
        'configs'              => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    public function incentivePackages(): HasMany {
        return $this->hasMany(IncentivePackage::class, 'project_code');
    }
}
