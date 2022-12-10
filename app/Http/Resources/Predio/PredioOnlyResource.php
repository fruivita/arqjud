<?php

namespace App\Http\Resources\Predio;

use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class PredioOnlyResource extends JsonResource
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
                'descricao' => $this->descricao,
                'localidade_id' => $this->localidade_id,
                'localidade' => LocalidadeOnlyResource::make($this->whenLoaded('localidade')),
                'andares_count' => $this->whenCounted('andares'),
            ]
            : [];
    }
}
