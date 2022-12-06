<?php

namespace App\Http\Resources\Processo;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class ProcessoCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ProcessoResource::class;

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
