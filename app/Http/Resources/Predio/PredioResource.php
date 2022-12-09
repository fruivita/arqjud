<?php

namespace App\Http\Resources\Predio;

use App\Enums\Policy;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Models\Andar;
use App\Models\Predio;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class PredioResource extends JsonResource
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
                'localidade_id' => $this->localidade_id,
                'localidade' => LocalidadeOnlyResource::make($this->whenLoaded('localidade')),
                'andares_count' => $this->whenCounted('andares'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Predio::class),
                        fn () => route('cadastro.predio.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Predio::class),
                        fn () => route('cadastro.predio.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        fn () => route('cadastro.predio.destroy', $this->id),
                    ),
                    'create_andar' => $this->when(
                        $request->user()->can(Policy::Create->value, Andar::class),
                        fn () => route('cadastro.andar.create', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
