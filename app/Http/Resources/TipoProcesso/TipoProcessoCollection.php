<?php

namespace App\Http\Resources\TipoProcesso;

use App\Enums\Policy;
use App\Models\TipoProcesso;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class TipoProcessoCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TipoProcessoResource::class;

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
                auth()->user()->can(Policy::Create->value, TipoProcesso::class),
                ['create' => route('cadastro.tipo-processo.create')]
            ),
        ];
    }
}
