<?php

namespace App\Enums;

/*
 * Tipos de ações disponíveis para manipulação de múltiplos checkbox
 * simultaneamente.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/collections
 */
enum AcaoCheckbox: string
{
    case SelecionarTodos = 'selecionar-todos';
    case DesmarcarTodos = 'desmarcar-todos';
    case SelecionarTodosNaPagina = 'selecionar-todos-na-pagina';
    case DesmarcarTodosNaPagina = 'desmarcar-todos-na-pagina';
    /**
     * Nome para exibição do tipo de ação disponível para os checkbox.
     *
     * @return string
     */
    public function nome()
    {
        return match ($this) {
            AcaoCheckbox::SelecionarTodos => __('Marcar todos'),
            AcaoCheckbox::DesmarcarTodos => __('Desmarcar todos'),
            AcaoCheckbox::SelecionarTodosNaPagina => __('Marcar todos na página'),
            AcaoCheckbox::DesmarcarTodosNaPagina => __('Desmarcar todos na página')
        };
    }

    /**
     * Todos os valores possíveis deste enum.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function valores()
    {
        return
        collect(AcaoCheckbox::cases())
        ->transform(function ($acao) {
            return $acao->value;
        });
    }
}
