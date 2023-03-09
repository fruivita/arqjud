<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\DB;

/**
 * Verifica se determinado valor existe em diversas colunas utilizando o
 * operador 'or'.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class MultiColumnExists implements InvokableRule
{
    /**
     * Tabela em que ocorrerá a pesquisa.
     *
     * @var string
     */
    private $tabela;

    /**
     * Colunas que serão verificadas.
     *
     * @var array<int, string>
     */
    private $colunas;

    /**
     * @param  array<int, string>  $colunas
     * @return void
     */
    public function __construct(string $tabela, array $colunas)
    {
        $this->tabela = $tabela;
        $this->colunas = $colunas;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value número do processo com ou sem a máscara
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $colunas = collect($this->colunas);

        if ($colunas->isEmpty()) {
            $fail('validation.exists')->translate();
        }

        $query = DB::table($this->tabela);

        $colunas->each(function (string $coluna) use ($query, $value) {
            $query->orWhere($coluna, $value);
        });

        if ($query->doesntExist()) {
            $fail('validation.exists')->translate();
        }
    }
}
