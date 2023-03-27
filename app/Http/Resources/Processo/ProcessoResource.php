<?php

namespace App\Http\Resources\Processo;

use App\Enums\Policy;
use App\Http\Resources\Caixa\CaixaEditResource;
use App\Models\Processo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class ProcessoResource extends JsonResource
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
                'numero_antigo' => $this->numero_antigo,
                'arquivado_em' => $this->arquivado_em->format('d-m-Y'),
                'guarda_permanente' => $this->guarda_permanente,
                'qtd_volumes' => $this->qtd_volumes,
                'descricao' => $this->descricao,
                'caixa_id' => $this->caixa_id,
                'processo_pai_id' => $this->processo_pai_id,
                'caixa' => CaixaEditResource::make($this->whenLoaded('caixa')),
                'processo_pai' => ProcessoResource::make($this->whenLoaded('processoPai')),
                'processos_filho_count' => $this->whenCounted('processosFilho'),
                'solicitacoes_count' => $this->whenCounted('solicitacoes'),
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::ViewOrUpdate->value, Processo::class),
                        route('cadastro.processo.edit', $this->id),
                    ),
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, Processo::class),
                        route('cadastro.processo.update', $this->id),
                    ),
                    'delete' => $this->when(
                        auth()->user()->can(Policy::Delete->value, $this->resource),
                        route('cadastro.processo.destroy', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
