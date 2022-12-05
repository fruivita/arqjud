<?php

namespace App\Http\Resources\VolumeCaixa;

use App\Enums\Policy;
use App\Http\Resources\Caixa\CaixaOnlyResource;
use App\Models\Processo;
use App\Models\VolumeCaixa;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class VolumeCaixaResource extends JsonResource
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
            'caixa_id' => $this->caixa_id,
            'caixa' => CaixaOnlyResource::make($this->whenLoaded('caixa')),
            'processos_count' => $this->whenCounted('processos'),
            'links' => [
                'create_processo' => $this->when(
                    $request->user()->can(Policy::Create->value, Processo::class),
                    fn () => route('cadastro.processo.create', $this->id),
                ),
                'view_or_update' => $this->when(
                    $request->user()->can(Policy::ViewOrUpdate->value, VolumeCaixa::class),
                    fn () => route('cadastro.volumeCaixa.edit', $this->id),
                ),
                'delete' => $this->when(
                    $request->user()->can(Policy::Delete->value, $this->resource),
                    fn () => route('cadastro.volumeCaixa.destroy', $this->id),
                ),
            ],
        ];
    }
}
