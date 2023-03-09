<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Translation extends Model {

    protected $fillable = [
        'key',
        'text',
        'tags',
        'is_published',
    ];

    protected $casts = [
        'text'         => 'array',
        'tags'         => 'array',
        'is_published' => 'boolean',
    ];

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param string|null $rawString
     *
     * @return array
     */
    public static function convertTagsToArray($rawString = null)
    {
        $list = [];

        if (empty($rawString)) {
            return $list;
        }

        foreach (explode(',', $rawString) as $rawTag) {
            if ($rawTag === '') {
                continue;
            }

            $list[] = Str::snake($rawTag);
        }

        return $list;
    }

    /**
     * @param array $tagsList
     *
     * @return string|null
     */
    public static function convertTagsToString(array $tagsList)
    {
        if (empty($tagsList)) {
            return null;
        }

        return implode(',', $tagsList);
    }
}
