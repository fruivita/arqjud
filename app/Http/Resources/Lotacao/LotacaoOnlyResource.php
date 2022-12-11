<?php

namespace App\Http\Resources\Lotacao;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class LotacaoOnlyResource extends JsonResource
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
                'nome' => $this->nome,
                'sigla' => $this->sigla,
                'lotacao_pai_id' => $this->lotacao_pai_id,
                'lotacao_pai' => LotacaoOnlyResource::make($this->whenLoaded('lotacaoPai')),
            ]
            : [];
    }
}
