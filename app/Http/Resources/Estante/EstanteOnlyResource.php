<?php

namespace App\Http\Resources\Estante;

use App\Http\Resources\Sala\SalaOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class EstanteOnlyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'sala_id' => $this->sala_id,
            'sala' => SalaOnlyResource::make($this->whenLoaded('sala')),
            'prateleiras_count' => $this->whenCounted('prateleiras'),
        ];
    }
}
