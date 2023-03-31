<?php

namespace App\Http\Resources\TipoProcesso;

use App\Enums\Policy;
use App\Models\TipoProcesso;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class TipoProcessoResource extends JsonResource
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
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'caixas_count' => $this->whenCounted('caixas'),
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::ViewOrUpdate->value, TipoProcesso::class),
                        route('cadastro.tipo-processo.edit', $this->id),
                    ),
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, TipoProcesso::class),
                        route('cadastro.tipo-processo.update', $this->id),
                    ),
                    'delete' => $this->when(
                        auth()->user()->can(Policy::Delete->value, $this->resource),
                        route('cadastro.tipo-processo.destroy', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
