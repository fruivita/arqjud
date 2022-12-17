<?php

namespace App\Http\Resources\Guia;

use App\Models\Processo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class GuiaOnlyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->prepare();

        return ($this->resource)
            ? [
                'id' => $this->id,
                'numero' => $this->numero,
                'ano' => $this->ano,
                'gerada_em' => $this->gerada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'remetente' => $this->remetente,
                'recebedor' => $this->recebedor,
                'lotacao_destinataria' => $this->lotacao_destinataria,
                'processos' => $this->processos,
            ]
            : [];
    }

    /**
     * Prepara os atributos do resource
     *
     * @return void
     */
    private function prepare()
    {
        if ($this->resource) {
            $this->lotacao_destinataria['sigla'] = str($this->lotacao_destinataria['sigla'])->upper();

            $this->processos->transform(function ($processo) {
                $processo['numero'] = mascara($processo['numero'], Processo::MASCARA_CNJ);

                return $processo;
            });
        }
    }
}
