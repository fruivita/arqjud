<?php

namespace App\Http\Resources\Usuario;

use App\Http\Resources\Cargo\CargoOnlyResource;
use App\Http\Resources\Funcao\FuncaoOnlyResource;
use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Http\Resources\Perfil\PerfilOnlyResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class UsuarioOnlyResource extends JsonResource
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
                'matricula' => $this->matricula,
                'username' => $this->username,
                'nome' => $this->nome,
                'email' => $this->email,
                'status' => $this->habilitado() ? __('completo') : __('incompleto'), // @phpstan-ignore-line
                'lotacao_id' => $this->lotacao_id,
                'cargo_id' => $this->cargo_id,
                'funcao_confianca_id' => $this->funcao_confianca_id,
                'perfil_id' => $this->perfil_id,
                'perfil_concedido_por' => $this->perfil_concedido_por,
                'antigo_perfil_id' => $this->antigo_perfil_id,
                'lotacao' => LotacaoOnlyResource::make($this->whenLoaded('lotacao')),
                'cargo' => CargoOnlyResource::make($this->whenLoaded('cargo')),
                'funcao' => FuncaoOnlyResource::make($this->whenLoaded('funcaoConfianca')),
                'perfil' => PerfilOnlyResource::make($this->whenLoaded('perfil')),
                'delegante' => UsuarioOnlyResource::make($this->whenLoaded('delegante')),
                'perfil_antigo' => PerfilOnlyResource::make($this->whenLoaded('perfilAntigo')),
            ]
            : [];
    }
}
