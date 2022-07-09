<?php

namespace App\Http\Livewire\Traits;

use Illuminate\Support\Facades\Validator;

/**
 * Trait para agrupar a lógica da pesquisa.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComPesquisa
{
    /**
     * Termo pesquisável informado pelo usuário.
     *
     * @var string
     */
    public $termo;

    /**
     * Atributos customizados para as query strings.
     *
     * @return array<string, mixed>
     */
    protected function queryString()
    {
        return [
            'termo' => ['except' => '',],
        ];
    }

    /**
     * Retorna a paginação à sua página inicial.
     *
     * Executado antes da propriedade `$termo` ser atualizada.
     *
     * @param mixed $valor
     *
     * @return void
     */
    public function updatingTermo($valor)
    {
        Validator::make(
            data: ['termo' => $valor],
            rules: ['termo' => ['nullable', 'string', 'max:50']],
            customAttributes: ['termo' => __('Termo pesquisável')]
        )->validate();

        $this->resetPage();
    }
}
