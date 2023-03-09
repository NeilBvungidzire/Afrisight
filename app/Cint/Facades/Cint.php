<?php

namespace App\Cint\Facades;

use App\Person;
use Illuminate\Support\Facades\Facade;

/**
 * Class Cint
 * @package App\Cint\Facades
 * @method static \App\Cint\Cint initialize(Person $person)
 *
 * @see \App\Cint\Cint
 */
class Cint extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'cint';
    }
}
