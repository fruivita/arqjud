<?php

namespace App\Http\Resources\Guia;

use App\Enums\Policy;
use App\Models\Guia;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class GuiaResource extends JsonResource
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
                'destino' => $this->destino,
                'processos' => $this->processos,
                'links' => $this->when(
                    auth()->user()->can(Policy::View->value, Guia::class),
                    [
                        'view' => route('atendimento.guia.show', $this->id),
                        'pdf' => route('atendimento.guia.pdf', $this->id),
                    ]
                ),
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
            $this->destino['sigla'] = str($this->destino['sigla'])->upper();

            $this->processos->transform(function ($processo) {
                $processo['numero'] = cnj($processo['numero']);

                return $processo;
            });
        }
    }
}
