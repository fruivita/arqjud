<?php

namespace App\Http\Resources\Localidade;

use App\Enums\Policy;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class LocalidadeResource extends JsonResource
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
                'predios_count' => $this->whenCounted('predios'),
                'caixas_criadas_count' => $this->whenCounted('caixasCriadas'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Localidade::class),
                        route('cadastro.localidade.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Localidade::class),
                        route('cadastro.localidade.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        route('cadastro.localidade.destroy', $this->id),
                    ),
                    'predio' => $this->when(
                        $request->user()->can(Policy::Create->value, Predio::class),
                        [
                            'create' => route('cadastro.predio.create', $this->id),
                            'store' => route('cadastro.predio.store', $this->id),
                        ]
                    ),
                ],
            ]
            : [];
    }
}
