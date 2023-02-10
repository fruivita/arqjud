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
                'nome' => $this->getFilename(), // @phpstan-ignore-line
                'links' => [
                    'view' => $this->when(
                        auth()->user()->can(Policy::LogView->value),
                        route('administracao.log.show', $this->getFilename()) // @phpstan-ignore-line
                    ),
                    'download' => $this->when(
                        auth()->user()->can(Policy::LogView->value),
                        route('administracao.log.download', $this->getFilename()) // @phpstan-ignore-line
                    ),
                ],
            ]
            : [];
    }
}
