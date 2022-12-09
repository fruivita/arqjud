<?php

namespace App\Http\Resources\Prateleira;

use App\Enums\Policy;
use App\Http\Resources\Estante\EstanteOnlyResource;
use App\Models\Prateleira;
use App\Models\Caixa;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class PrateleiraResource extends JsonResource
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
                'estante_id' => $this->estante_id,
                'estante' => EstanteOnlyResource::make($this->whenLoaded('estante')),
                'caixas_count' => $this->whenCounted('caixas'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Prateleira::class),
                        fn () => route('cadastro.prateleira.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Prateleira::class),
                        fn () => route('cadastro.prateleira.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        fn () => route('cadastro.prateleira.destroy', $this->id),
                    ),
                    'create_caixa' => $this->when(
                        $request->user()->can(Policy::Create->value, Caixa::class),
                        fn () => route('cadastro.caixa.create', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
