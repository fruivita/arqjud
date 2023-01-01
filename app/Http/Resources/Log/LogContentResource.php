<?php

namespace App\Http\Resources\Log;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class LogContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return ($this->resource)
            ? [
                'linha' => $this->resource,
            ]
            : [];
    }
}
