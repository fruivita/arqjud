<?php

namespace App\Http\Resources\Permissao;

use App\Enums\Policy;
use App\Models\Permissao;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class PermissaoResource extends JsonResource
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
                'slug' => $this->slug,
                'descricao' => $this->descricao,
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::ViewOrUpdate->value, Permissao::class),
                        route('administracao.permissao.edit', $this->id),
                    ),
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, Permissao::class),
                        route('administracao.permissao.update', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
