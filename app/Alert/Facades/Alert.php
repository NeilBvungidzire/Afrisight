<?php

namespace App\Alert\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Alert
 * @package App\Alert\Facades
 * @method static \App\Alert\Alert makePrimary(string $message = null)
 * @method static \App\Alert\Alert makeSecondary(string $message = null)
 * @method static \App\Alert\Alert makeSuccess(string $message = null)
 * @method static \App\Alert\Alert makeDanger(string $message = null)
 * @method static \App\Alert\Alert makeWarning(string $message = null)
 * @method static \App\Alert\Alert makeInfo(string $message = null)
 * @method static \App\Alert\Alert makeLight(string $message = null)
 * @method static \App\Alert\Alert makeDark(string $message = null)
 * @method static \App\Alert\Alert setHeading(string $text)
 * @method static \App\Alert\Alert setBody(string $text)
 * @see \App\Alert\Alert
 */
class Alert extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'alert';
    }
}
