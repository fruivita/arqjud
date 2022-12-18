<?php

namespace App\Http\Resources\Usuario;

use App\Http\Resources\Cargo\CargoOnlyResource;
use App\Http\Resources\Lotacao\LotacaoOnlyResource;
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
                'lotacao_id' => $this->lotacao_id,
                'cargo_id' => $this->cargo_id,
                'lotacao' => LotacaoOnlyResource::make($this->whenLoaded('lotacao')),
                'cargo' => CargoOnlyResource::make($this->whenLoaded('cargo')),
            ]
            : [];
    }
}
