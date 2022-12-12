<?php

namespace App\Http\Resources\Solicitacao;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class CounterResource extends JsonResource
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
                'solicitadas' => isset($this->solicitadas) ? $this->solicitadas : null,
                'entregues' => isset($this->entregues) ? $this->entregues : null,
                'devolvidas' => isset($this->devolvidas) ? $this->devolvidas : null,
            ]
            : [];
    }
}
