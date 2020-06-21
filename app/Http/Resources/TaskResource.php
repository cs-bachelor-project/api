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
            'user_id' =>$this->user_id,
            'user_name' => optional($this->user)->name,
            'details' => TaskDetailResource::collection($this->whenLoaded('details')),
            'cancellation' => new TaskCancellationResource($this->whenLoaded('cancellation')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
