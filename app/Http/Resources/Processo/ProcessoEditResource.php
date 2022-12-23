<?php

namespace App\Http\Resources\Processo;

use App\Enums\Policy;
use App\Http\Resources\VolumeCaixa\VolumeCaixaEditResource;
use App\Models\Processo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class ProcessoEditResource extends JsonResource
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
                'guarda_permanente' => $this->guarda_permanente ? __('Sim') : __('Não'),
                'qtd_volumes' => $this->qtd_volumes,
                'descricao' => $this->descricao,
                'volume_caixa_id' => $this->volume_caixa_id,
                'processo_pai_id' => $this->processo_pai_id,
                'volume_caixa' => VolumeCaixaEditResource::make($this->whenLoaded('volumeCaixa')),
                'processo_pai' => ProcessoEditResource::make($this->whenLoaded('processoPai')),
                'processos_filho_count' => $this->whenCounted('processosFilho'),
                'solicitacoes_count' => $this->whenCounted('solicitacoes'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Processo::class),
                        route('cadastro.processo.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Processo::class),
                        route('cadastro.processo.update', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
