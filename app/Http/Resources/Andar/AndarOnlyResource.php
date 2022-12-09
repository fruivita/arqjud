<?php

namespace App\Http\Resources\Andar;

use App\Http\Resources\Predio\PredioOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class AndarOnlyResource extends JsonResource
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
                'numero' => $this->numero,
                'apelido' => $this->apelido,
                'predio_id' => $this->predio_id,
                'predio' => PredioOnlyResource::make($this->whenLoaded('predio')),
                'salas_count' => $this->whenCounted('salas'),
            ]
            : [];
    }
}
