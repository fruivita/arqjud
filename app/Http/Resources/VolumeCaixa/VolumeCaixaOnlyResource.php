<?php

namespace App\Http\Resources\VolumeCaixa;

use App\Http\Resources\Caixa\CaixaOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class VolumeCaixaOnlyResource extends JsonResource
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
                'caixa_id' => $this->caixa_id,
                'caixa' => CaixaOnlyResource::make($this->whenLoaded('caixa')),
                'processos_count' => $this->whenCounted('processos'),
            ]
            : [];
    }
}
