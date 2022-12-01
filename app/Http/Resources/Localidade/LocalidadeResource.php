<?php

namespace App\Http\Resources\Localidade;

use App\Enums\Policy;
use App\Models\Localidade;
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
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'view_or_update' => $this->when(
                $request->user()->can(Policy::ViewOrUpdate->value, Localidade::class),
                fn () => route('cadastro.localidade.edit', $this->id),
            ),
            'delete' => $this->when(
                $request->user()->can(Policy::Delete->value, $this->resource),
                fn () => route('cadastro.localidade.destroy', $this->id),
            ),
        ];
    }
}
