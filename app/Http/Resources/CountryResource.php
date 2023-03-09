<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CountryResource extends JsonResource {

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $attributes = $this->getAttributes();
        $return = [];

        foreach ($attributes as $name => $value) {
            $return[Str::camel($name)] = $value;
        }

        return $return;
    }
}
