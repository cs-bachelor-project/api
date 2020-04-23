<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'person_name' => $this->person_name,
            'note' => $this->note,
            'user_id' => $this->user_id,
            'details' => TaskDetailResource::collection($this->whenLoaded('details')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
