<?php

namespace App\Http\Resources\Estante;

use App\Enums\Policy;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Estante;
use App\Models\Prateleira;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class EstanteResource extends JsonResource
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
                'sala_id' => $this->sala_id,
                'sala' => SalaResource::make($this->whenLoaded('sala')),
                'prateleiras_count' => $this->whenCounted('prateleiras'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Estante::class),
                        fn () => route('cadastro.estante.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Estante::class),
                        fn () => route('cadastro.estante.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        fn () => route('cadastro.estante.destroy', $this->id),
                    ),
                    'create_prateleira' => $this->when(
                        $request->user()->can(Policy::Create->value, Prateleira::class),
                        fn () => route('cadastro.prateleira.create', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
