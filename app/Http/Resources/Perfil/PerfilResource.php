<?php

namespace App\Http\Resources\Perfil;

use App\Enums\Policy;
use App\Models\Perfil;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class PerfilResource extends JsonResource
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
                'poder' => $this->poder,
                'descricao' => $this->descricao,
                'usuarios_count' => $this->whenCounted('usuarios'),
                'delegados_count' => $this->whenCounted('delegados'),
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::ViewOrUpdate->value, Perfil::class),
                        route('administracao.perfil.edit', $this->id),
                    ),
                    'update' => $this->when(
                        auth()->user()->can(Policy::Update->value, Perfil::class),
                        route('administracao.perfil.update', $this->id),
                    ),
                    'delete' => $this->when(
                        auth()->user()->can(Policy::Delete->value, $this->resource),
                        route('administracao.perfil.destroy', $this->id),
                    ),
                ],
            ]
            : [];
    }
}
