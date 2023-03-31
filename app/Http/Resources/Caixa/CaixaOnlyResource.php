<?php

namespace App\Http\Resources\Caixa;

use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraOnlyResource;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\TipoProcesso\TipoProcessoOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class CaixaOnlyResource extends JsonResource
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
                'prateleira' => PrateleiraOnlyResource::make($this->whenLoaded('prateleira')),
                'localidade_criadora' => LocalidadeOnlyResource::make($this->whenLoaded('localidadeCriadora')),
                'tipo_processo' => TipoProcessoOnlyResource::make($this->whenLoaded('tipoProcesso')),
                'processos' => ProcessoOnlyResource::collection($this->whenLoaded('processos')),
                'processos_count' => $this->whenCounted('processos'),
            ]
            : [];
    }
}
