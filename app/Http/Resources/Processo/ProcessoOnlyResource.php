<?php

namespace App\Http\Resources\Processo;

use App\Http\Resources\VolumeCaixa\VolumeCaixaOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class ProcessoOnlyResource extends JsonResource
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
            'numero_antigo' => $this->numero_antigo,
            'arquivado_em' => $this->arquivado_em->format('d-m-Y'),
            'guarda_permanente' => $this->guarda_permanente ? __('Sim') : __('Não'),
            'qtd_volumes' => $this->qtd_volumes,
            'volume_caixa_id' => $this->volume_caixa_id,
            'processo_pai_id' => $this->processo_pai_id,
            'volume_caixa' => VolumeCaixaOnlyResource::make($this->whenLoaded('volumeCaixa')),
            'processo_pai' => ProcessoOnlyResource::make($this->whenLoaded('processoPai')),
            'processos_filho_count' => $this->whenCounted('processosFilho'),
            'solicitacoes_count' => $this->whenCounted('solicitacoes'),
        ];
    }
}
