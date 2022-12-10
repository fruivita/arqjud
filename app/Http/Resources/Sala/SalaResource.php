<?php

namespace App\Http\Resources\Sala;

use App\Enums\Policy;
use App\Http\Resources\Andar\AndarResource;
use App\Models\Estante;
use App\Models\Sala;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class SalaResource extends JsonResource
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
                'andar' => AndarResource::make($this->whenLoaded('andar')),
                'estantes_count' => $this->whenCounted('estantes'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Sala::class),
                        fn () => route('cadastro.sala.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Sala::class),
                        fn () => route('cadastro.sala.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        fn () => route('cadastro.sala.destroy', $this->id),
                    ),
                    'create_estante' => $this->when(
                        $request->user()->can(Policy::Create->value, Estante::class),
                        fn () => route('cadastro.estante.create', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
