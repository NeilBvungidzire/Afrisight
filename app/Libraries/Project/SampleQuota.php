<?php

namespace App\Libraries\Project;

use RuntimeException;

/**
 * @property int        $id
 * @property string     $group
 * @property int        $size
 * @property float|null $weight
 * @property array      $selectors Key, value array with key the selector name and key the id of selector.
 */
class SampleQuota {

    private $attributes = [
        'id',
        'group',
        'size',
        'weight',
        'selectors',
    ];

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed|void
     */
    public function __get(string $key)
    {
        if ( ! in_array($key, $this->attributes)) {
            throw new RuntimeException("Attribute ${key} is not allowed.");
        }

        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function __set(string $key, $value)
    {
        if ( ! in_array($key, $this->attributes)) {
            throw new RuntimeException("Attribute ${key} is not allowed.");
        }

        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param string $key
     * @return mixed|void
     */
    private function getAttribute(string $key)
    {
        if ( ! isset($key)) {
            return;
        }

        return $this->{$key};
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    private function setAttribute(string $key, $value): void
    {
        if ( ! isset($key)) {
            return;
        }

        $this->{$key} = $value;
    }
}
