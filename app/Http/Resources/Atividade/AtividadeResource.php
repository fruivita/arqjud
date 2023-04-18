<?php

namespace App\Http\Resources\Atividade;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class AtividadeResource extends JsonResource
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
                'id' => $this->id,
                'log_name' => $this->log_name,
                'description' => $this->description,
                'subject_type' => $this->subject_type,
                'event' => $this->event,
                'subject_id' => $this->subject_id,
                'causer_type' => $this->causer_type,
                'causer_id' => $this->causer_id,
                'matricula' => $this->matricula,
                'properties' => $this->properties,
                'uuid' => $this->batch_uuid,
                'created_at' => $this->created_at->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'updated_at' => $this->updated_at->tz(config('app.tz'))->format('d-m-Y H:i:s'),
            ]
            : [];
    }
}
