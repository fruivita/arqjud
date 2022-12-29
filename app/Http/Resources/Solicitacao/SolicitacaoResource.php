<?php

namespace App\Http\Resources\Solicitacao;

use App\Enums\Policy;
use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class SolicitacaoResource extends JsonResource
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
                'solicitada_em' => $this->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'entregue_em' => empty($this->entregue_em) ? null : $this->entregue_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'devolvida_em' => empty($this->devolvida_em) ? null : $this->devolvida_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'por_guia' => $this->por_guia,
                'descricao' => $this->descricao,
                'status' => $this->status,
                'processo_id' => $this->processo_id,
                'solicitante_id' => $this->solicitante_id,
                'recebedor_id' => $this->recebedor_id,
                'remetente_id' => $this->remetente_id,
                'rearquivador_id' => $this->rearquivador_id,
                'lotacao_destinataria_id' => $this->lotacao_destinataria_id,
                'guia_id' => $this->guia_id,
                'processo' => ProcessoOnlyResource::make($this->whenLoaded('processo')),
                'solicitante' => UsuarioOnlyResource::make($this->whenLoaded('solicitante')),
                'recebedor' => UsuarioOnlyResource::make($this->whenLoaded('recebedor')),
                'remetente' => UsuarioOnlyResource::make($this->whenLoaded('remetente')),
                'rearquivador' => UsuarioOnlyResource::make($this->whenLoaded('rearquivador')),
                'lotacao_destinataria' => LotacaoOnlyResource::make($this->whenLoaded('lotacaoDestinataria')),
                'links' => [
                    'delete' => $this->when(
                        auth()->user()->can(Policy::Delete->value, $this->resource),
                        route('atendimento.solicitar-processo.destroy', $this->id),
                    ),
                    'externo_delete' => $this->when(
                        auth()->user()->can(Policy::ExternoDelete->value, $this->resource),
                        route('solicitacao.destroy', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
