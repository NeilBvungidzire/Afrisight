<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlexTable extends Model {

    use SoftDeletes;

    protected $fillable = [
        'reference_code',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
