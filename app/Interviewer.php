<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interviewer extends Model {

    use SoftDeletes;

    protected $fillable = [
        'key',
        'first_name',
        'last_name',
        'sample_code',
    ];
}
