<?php

namespace App\Http\Resources\Caixa;

use App\Enums\Policy;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraOnlyResource;
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
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'ano' => $this->ano,
            'guarda_permanente' => $this->guarda_permanente ? __('Sim') : __('Não'),
            'complemento' => $this->complemento,
            'prateleira_id' => $this->prateleira_id,
            'localidade_criadora_id' => $this->localidade_criadora_id,
            'prateleira' => PrateleiraOnlyResource::make($this->whenLoaded('prateleira')),
            'localidade_criadora' => LocalidadeOnlyResource::make($this->whenLoaded('localidadeCriadora')),
            'volumes_count' => $this->whenCounted('volumes'),
            'links' => [
                'create_volume' => $this->when(
                    $request->user()->can(Policy::Create->value, VolumeCaixa::class),
                    fn () => route('cadastro.volumeCaixa.create', $this->id),
                ),
                'view_or_update' => $this->when(
                    $request->user()->can(Policy::ViewOrUpdate->value, Caixa::class),
                    fn () => route('cadastro.caixa.edit', $this->id),
                ),
                'delete' => $this->when(
                    $request->user()->can(Policy::Delete->value, $this->resource),
                    fn () => route('cadastro.caixa.destroy', $this->id),
                ),
            ],
        ];
    }
}
