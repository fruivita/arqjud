<?php

namespace App\Http\Resources\Sala;

use App\Http\Resources\Andar\AndarOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class SalaOnlyResource extends JsonResource
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
                'descricao' => $this->descricao,
                'andar_id' => $this->andar_id,
                'andar' => AndarOnlyResource::make($this->whenLoaded('andar')),
                'estantes_count' => $this->whenCounted('estantes'),
            ]
            : [];
    }
}
