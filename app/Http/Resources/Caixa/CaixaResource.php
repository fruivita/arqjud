<?php

namespace App\Http\Resources\Caixa;

use App\Enums\Policy;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class CaixaResource extends JsonResource
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
                'ano' => $this->ano,
                'guarda_permanente' => $this->guarda_permanente ? __('Sim') : __('Não'),
                'complemento' => $this->complemento,
                'descricao' => $this->descricao,
                'prateleira_id' => $this->prateleira_id,
                'localidade_criadora_id' => $this->localidade_criadora_id,
                'prateleira' => PrateleiraResource::make($this->whenLoaded('prateleira')),
                'localidade_criadora' => LocalidadeResource::make($this->whenLoaded('localidadeCriadora')),
                'volumes_count' => $this->whenCounted('volumes'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, Caixa::class),
                        fn () => route('cadastro.caixa.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, Caixa::class),
                        fn () => route('cadastro.caixa.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        fn () => route('cadastro.caixa.destroy', $this->id),
                    ),
                    'create_volume' => $this->when(
                        $request->user()->can(Policy::Create->value, VolumeCaixa::class),
                        fn () => route('cadastro.volumeCaixa.create', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
