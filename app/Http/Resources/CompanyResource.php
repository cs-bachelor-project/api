<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'cvr' => $this->cvr,
            'name' => $this->name,
            'country' => $this->country,
            'postal' => $this->postal,
            'city' => $this->city,
            'street' => $this->street,
            'street_number' => $this->street_number,
        ];
    }
}
