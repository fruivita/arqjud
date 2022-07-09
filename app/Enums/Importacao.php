<?php

namespace App\Enums;

/*
 * Tipos de importações que a aplicação está preparada para fazer.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/collections
 */
enum Importacao: string
{
    case Corporativo = 'corporativo';
    /**
     * Nome para exibição do tipo de importação.
     *
     * @return string
     */
    public function nome()
    {
        return match ($this) {
            Importacao::Corporativo => __('Estrutura corporativa')
        };
    }

    /**
     * Mapeamento da queue utilizada para cada tipo de importação.
     *
     * @return string
     */
    public function queue()
    {
        return match ($this) {
            Importacao::Corporativo => Queue::Corporativo->value
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
        collect(Importacao::cases())
        ->transform(function ($importacao) {
            return $importacao->value;
        });
    }
}
