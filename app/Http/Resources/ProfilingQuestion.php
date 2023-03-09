<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilingQuestion extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $return = [
            'id'           => $this->id,
            'uuid'         => $this->uuid,
            'title'        => $this->title,
            'type'         => $this->type,
            'isPublished'  => $this->is_published,
            'isDefinitive' => $this->is_definitive,
            'settings'     => $this->settings,
            'sort'         => $this->sort,
            'answerParams' => $this->answer_params,
            'conditions'   => $this->conditions,
            'deletedAt'    => $this->deleted_at,
            'updatedAt'    => $this->updated_at,
            'createdAt'    => $this->created_at,
        ];

        return $return;
    }
}
