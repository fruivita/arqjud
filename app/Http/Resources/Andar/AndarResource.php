<?php

namespace App\Http\Resources\Andar;

use App\Enums\Policy;
use App\Http\Resources\Predio\PredioResource;
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
                'predio' => PredioResource::make($this->whenLoaded('predio')),
                'salas_count' => $this->whenCounted('salas'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Andar::class),
                        fn () => route('cadastro.andar.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Andar::class),
                        fn () => route('cadastro.andar.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        fn () => route('cadastro.andar.destroy', $this->id),
                    ),
                    'create_sala' => $this->when(
                        $request->user()->can(Policy::Create->value, Sala::class),
                        fn () => route('cadastro.sala.create', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
