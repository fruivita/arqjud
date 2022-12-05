<?php

namespace App\Http\Resources\Caixa;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class CaixaCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CaixaResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return ['data' => $this->collection];
    }
}
