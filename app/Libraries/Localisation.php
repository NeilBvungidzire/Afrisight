<?php


namespace App\Libraries;


class Localisation
{
    /**
     * @return string
     */
    public static function stringifyLanguageList()
    {
        return implode('|', config('app.available_languages'));
    }

    /**
     * @param string $language
     */
    public static function setLanguage(string $language)
    {
        app()->setLocale($language);
        session()->put('locale', $language);
    }
}
