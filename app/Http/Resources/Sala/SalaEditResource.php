<?php

namespace App\Http\Resources\Sala;

use App\Enums\Policy;
use App\Http\Resources\Andar\AndarEditResource;
use App\Models\Estante;
use App\Models\Sala;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class SalaEditResource extends JsonResource
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
                'descricao' => $this->descricao,
                'andar_id' => $this->andar_id,
                'andar' => AndarEditResource::make($this->whenLoaded('andar')),
                'estantes_count' => $this->whenCounted('estantes'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Sala::class),
                        route('cadastro.sala.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Sala::class),
                        route('cadastro.sala.update', $this->id),
                    ),
                    'estante' => $this->when(
                        $request->user()->can(Policy::Create->value, Estante::class),
                        [
                            'create' => route('cadastro.estante.create', $this->id),
                            'store' => route('cadastro.estante.store', $this->id),
                        ],
                    ),
                ],
            ]
            : [];
    }
}
