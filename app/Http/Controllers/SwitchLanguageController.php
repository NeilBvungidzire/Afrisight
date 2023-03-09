<?php

namespace App\Http\Controllers;

class SwitchLanguageController extends Controller {

    public function __invoke()
    {
        $language = request('lang');
        $previousRouteName = app('router')
            ->getRoutes()
            ->match(app('request')->create(url()->previous()))
            ->getName();

        return redirect()->route($previousRouteName, ['language' => $language]);
    }
}
