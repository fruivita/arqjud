<?php

namespace App\Http\Resources\VolumeCaixa;

use App\Enums\Policy;
use App\Http\Resources\Caixa\CaixaResource;
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
        return ($this->resource)
            ? [
                'id' => $this->id,
                'numero' => $this->numero,
                'descricao' => $this->descricao,
                'caixa_id' => $this->caixa_id,
                'caixa' => CaixaResource::make($this->whenLoaded('caixa')),
                'processos_count' => $this->whenCounted('processos'),
                'links' => [
                    'view' => $this->when(
                        $request->user()->can(Policy::ViewOrUpdate->value, VolumeCaixa::class),
                        fn () => route('cadastro.volume-caixa.edit', $this->id),
                    ),
                    'update' => $this->when(
                        $request->user()->can(Policy::Update->value, VolumeCaixa::class),
                        fn () => route('cadastro.volume-caixa.update', $this->id),
                    ),
                    'delete' => $this->when(
                        $request->user()->can(Policy::Delete->value, $this->resource),
                        fn () => route('cadastro.volume-caixa.destroy', $this->id),
                    ),
                    'create_processo' => $this->when(
                        $request->user()->can(Policy::Create->value, Processo::class),
                        fn () => route('cadastro.processo.create', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
