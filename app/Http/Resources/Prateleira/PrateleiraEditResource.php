<?php

namespace App\Http\Resources\Prateleira;

use App\Enums\Policy;
use App\Http\Resources\Estante\EstanteEditResource;
use App\Models\Caixa;
use App\Models\Prateleira;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class PrateleiraEditResource extends JsonResource
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
                'estante_id' => $this->estante_id,
                'estante' => EstanteEditResource::make($this->whenLoaded('estante')),
                'caixas_count' => $this->whenCounted('caixas'),
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::ViewOrUpdate->value, Prateleira::class),
                        route('cadastro.prateleira.edit', $this->id),
                    ),
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, Prateleira::class),
                        route('cadastro.prateleira.update', $this->id),
                    ),
                    'caixa' => $this->when(
                        auth()->user()->can(Policy::Create->value, Caixa::class),
                        [
                            'create' => route('cadastro.caixa.create', $this->id),
                            'store' => route('cadastro.caixa.store', $this->id),
                        ],
                    ),
                ],
            ]
            : [];
    }
}
