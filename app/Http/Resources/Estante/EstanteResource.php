<?php

namespace App\Http\Resources\Estante;

use App\Enums\Policy;
use App\Http\Resources\Sala\SalaOnlyResource;
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
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'sala_id' => $this->sala_id,
            'sala' => SalaOnlyResource::make($this->whenLoaded('sala')),
            'prateleiras_count' => $this->whenCounted('prateleiras'),
            'links' => [
                'create_prateleira' => $this->when(
                    $request->user()->can(Policy::Create->value, Prateleira::class),
                    fn () => route('cadastro.prateleira.create', $this->id),
                ),
                'view_or_update' => $this->when(
                    $request->user()->can(Policy::ViewOrUpdate->value, Estante::class),
                    fn () => route('cadastro.estante.edit', $this->id),
                ),
                'delete' => $this->when(
                    $request->user()->can(Policy::Delete->value, $this->resource),
                    fn () => route('cadastro.estante.destroy', $this->id),
                ),
            ],
        ];
    }
}
