<?php

namespace App\Http\Resources\Andar;

use App\Enums\Policy;
use App\Http\Resources\Predio\PredioEditResource;
use App\Models\Andar;
use App\Models\Sala;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class AndarResource extends JsonResource
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
                'apelido' => $this->apelido,
                'descricao' => $this->descricao,
                'predio_id' => $this->predio_id,
                'predio' => PredioEditResource::make($this->whenLoaded('predio')),
                'salas_count' => $this->whenCounted('salas'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Andar::class),
                        route('cadastro.andar.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Andar::class),
                        route('cadastro.andar.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        route('cadastro.andar.destroy', $this->id),
                    ),
                    'sala' => $this->when(
                        $request->user()->can(Policy::Create->value, Sala::class),
                        [
                            'create' => route('cadastro.sala.create', $this->id),
                            'store' => route('cadastro.sala.store', $this->id),
                        ],
                    ),
                ],
            ]
            : [];
    }
}
