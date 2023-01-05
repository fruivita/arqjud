<?php

namespace App\Http\Resources\Caixa;

use App\Enums\Policy;
use App\Http\Resources\Localidade\LocalidadeEditResource;
use App\Http\Resources\Prateleira\PrateleiraEditResource;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class CaixaEditResource extends JsonResource
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
                'ano' => $this->ano,
                'guarda_permanente' => $this->guarda_permanente,
                'complemento' => $this->complemento,
                'descricao' => $this->descricao,
                'prateleira_id' => $this->prateleira_id,
                'localidade_criadora_id' => $this->localidade_criadora_id,
                'prateleira' => PrateleiraEditResource::make($this->whenLoaded('prateleira')),
                'localidade_criadora' => LocalidadeEditResource::make($this->whenLoaded('localidadeCriadora')),
                'volumes_count' => $this->whenCounted('volumes'),
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::ViewOrUpdate->value, Caixa::class),
                        route('cadastro.caixa.edit', $this->id),
                    ),
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, Caixa::class),
                        route('cadastro.caixa.update', $this->id),
                    ),
                    'volume' => $this->when(
                        auth()->user()->can(Policy::Create->value, VolumeCaixa::class),
                        [
                            'create' => route('cadastro.volume-caixa.create', $this->id),
                            'store' => route('cadastro.volume-caixa.store', $this->id),
                        ],
                    ),
                ],
            ]
            : [];
    }
}
