<?php

namespace App\Http\Resources\Prateleira;

use App\Http\Resources\Estante\EstanteOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class PrateleiraOnlyResource extends JsonResource
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
            'estante_id' => $this->estante_id,
            'estante' => EstanteOnlyResource::make($this->whenLoaded('estante')),
            'caixas_count' => $this->whenCounted('caixas'),
        ];
    }
}
