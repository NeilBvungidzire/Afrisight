<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalPractitioner extends Model {

    use SoftDeletes;

    protected $fillable = [
        'email',
        'mobile_number',
        'first_name',
        'last_name',
        'profession',
        'other_data',
    ];

    protected $casts = [
        'other_data'    => 'array',
    ];
}
