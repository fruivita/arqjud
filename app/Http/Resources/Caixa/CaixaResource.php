<?php

namespace App\Http\Resources\Caixa;

use App\Enums\Policy;
use App\Http\Resources\Localidade\LocalidadeEditResource;
use App\Http\Resources\Prateleira\PrateleiraEditResource;
use App\Http\Resources\TipoProcesso\TipoProcessoEditResource;
use App\Models\Caixa;
use App\Models\Processo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class CaixaResource extends JsonResource
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
                'tipo_processo_id' => $this->tipo_processo_id,
                'prateleira' => PrateleiraEditResource::make($this->whenLoaded('prateleira')),
                'localidade_criadora' => LocalidadeEditResource::make($this->whenLoaded('localidadeCriadora')),
                'tipo_processo' => TipoProcessoEditResource::make($this->whenLoaded('tipoProcesso')),
                'processos_count' => $this->whenCounted('processos'),
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::ViewOrUpdate->value, Caixa::class),
                        route('cadastro.caixa.edit', $this->id),
                    ),
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, Caixa::class),
                        route('cadastro.caixa.update', $this->id),
                    ),
                    'delete' => $this->when(
                        auth()->user()->can(Policy::Delete->value, $this->resource),
                        route('cadastro.caixa.destroy', $this->id),
                    ),
                    'processo' => $this->when(
                        auth()->user()->can(Policy::Create->value, Processo::class),
                        [
                            'create' => route('cadastro.processo.create', $this->id),
                            'store' => route('cadastro.processo.store', $this->id),
                        ],
                    ),
                ],
            ]
            : [];
    }
}
