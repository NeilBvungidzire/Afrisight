<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CintQuestion extends Model {

    protected $fillable = [
        'country_id',
        'file',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
