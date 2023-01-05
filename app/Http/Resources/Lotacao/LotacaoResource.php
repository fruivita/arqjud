<?php

namespace App\Http\Resources\Lotacao;

use App\Enums\Policy;
use App\Models\Lotacao;
use App\Models\Predio;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class LotacaoResource extends JsonResource
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
                'sigla' => str($this->sigla)->upper(),
                'administravel' => $this->administravel,
                'usuarios_count' => $this->whenCounted('usuarios'),
                'lotacao_pai_id' => $this->lotacao_pai,
                'lotacao_pai' => LotacaoOnlyResource::make($this->whenLoaded('lotacaoPai')),
                'usuarios_count' => $this->whenCounted('usuarios'),
                'links' => [
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, Lotacao::class),
                        route('administracao.lotacao.update', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
