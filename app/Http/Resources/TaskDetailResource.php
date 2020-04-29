<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailResource extends JsonResource
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
            'country' => $this->country,
            'city' => $this->city,
            'street' => $this->street,
            'street_number' => $this->street_number,
            'phone' => $this->phone,
            'action' => $this->action,
            'scheduled_at' => is_null($this->scheduled_at) ? null : $this->scheduled_at->toDateTimeString(),
            'completed_at' => is_null($this->completed_at) ? null : $this->completed_at->toDateTimeString(),
            'task' => new TaskResource($this->whenLoaded('task')),
        ];
    }
}
