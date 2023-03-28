<?php

namespace App\Http\Resources\Processo;

use App\Http\Resources\Caixa\CaixaOnlyResource;
use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
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
        return ($this->resource)
            ? [
                'id' => $this->id,
                'numero' => $this->numero,
                'numero_antigo' => $this->numero_antigo,
                'arquivado_em' => $this->arquivado_em->format('d-m-Y'),
                'guarda_permanente' => $this->guarda_permanente,
                'qtd_volumes' => $this->qtd_volumes,
                'vol_caixa_inicial' => $this->vol_caixa_inicial,
                'vol_caixa_final' => $this->vol_caixa_final,
                'descricao' => $this->descricao,
                'caixa_id' => $this->caixa_id,
                'processo_pai_id' => $this->processo_pai_id,
                'caixa' => CaixaOnlyResource::make($this->whenLoaded('caixa')),
                'processo_pai' => ProcessoOnlyResource::make($this->whenLoaded('processoPai')),
                'processos_filho' => ProcessoOnlyResource::collection($this->whenLoaded('processosFilho')),
                'solicitacao_ativa' => SolicitacaoOnlyResource::collection($this->whenLoaded('solicitacoesAtivas')),
                'processos_filho_count' => $this->whenCounted('processosFilho'),
                'solicitacoes_count' => $this->whenCounted('solicitacoes'),
            ]
            : [];
    }
}
