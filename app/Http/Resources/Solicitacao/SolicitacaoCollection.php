<?php

namespace App\Http\Resources\Solicitacao;

use App\Enums\Policy;
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
            'links' => $this->when(
                $request->user()->can(Policy::Create->value, Solicitacao::class),
                ['create' => route('solicitacao.create')]
            ),
        ];
    }
}
