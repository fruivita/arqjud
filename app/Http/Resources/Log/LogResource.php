<?php

namespace App\Http\Resources\Log;

use App\Enums\Policy;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @see https://laravel.com/docs/9.x/eloquent-resources
 */
class LogResource extends JsonResource
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
                'nome' => $this->getFilename(),
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::LogView->value),
                        route('administracao.log.show', $this->getFilename())
                    ),
                    'download' => $this->when(
                        auth()->user()->can(Policy::LogView->value),
                        route('administracao.log.download', $this->getFilename())
                    ),
                    'delete' => $this->when(
                        auth()->user()->can(Policy::LogDelete->value),
                        route('administracao.log.destroy', $this->getFilename())
                    ),
                ],
            ]
            : [];
    }
}
