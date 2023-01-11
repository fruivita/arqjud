<?php

namespace App\Rules;

use App\Models\Solicitacao;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Query\Builder;

/**
 * Verifica se a solicitação do processo está em condições de ser executada,
 * isto é, de se entregar os processos solicitados.
 *
 * A verificação consiste em duas checagens
 * 1. A solicitação deve estar no status solicitada;
 * 2. O usuário recebedor deve estar lotado na lotação (destino) da
 * solicitação.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class SolicitacaoEntregavel implements InvokableRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value id da solicitação
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $entregavel = Solicitacao::query()
            ->where('id', $value)
            ->where('destino_id', function (Builder $query) {
                $query->select(['lotacao_id'])
                    ->from('usuarios')
                    ->where('matricula', $this->data['recebedor'])
                    ->limit(1);
            })
            ->solicitadas()
            ->exists();

        if ($entregavel !== true) {
            $fail('validation.solicitacao.recebedor')->translate();
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
