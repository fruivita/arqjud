<?php

namespace App\Http\Resources\Solicitacao;

use App\Enums\Policy;
use App\Models\Solicitacao;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class SolicitacaoCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = SolicitacaoResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'links' => [
                'create' => $this->when(
                    $request->user()->can(Policy::Create->value, Solicitacao::class),
                    route('atendimento.solicitar-processo.create')
                ),
                'externo_create' => $this->when(
                    $request->user()->can(Policy::ExternoCreate->value, Solicitacao::class),
                    route('solicitacao.create')
                ),
            ],
        ];
    }
}
