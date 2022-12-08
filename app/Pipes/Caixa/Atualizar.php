<?php

namespace App\Pipes\Caixa;

use App\Models\Caixa;
use Closure;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Atualizar
{
    /**
     * Pipe de atualização dos atributos da caixa de acordo com os dados,
     * presumidamente, validados e presentes no request.
     *
     * @param  \App\Models\Caixa  $caixa
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Caixa $caixa, Closure $next)
    {
        request()->whenFilled('numero', fn ($numero) => $caixa->numero = $numero);
        request()->whenFilled('ano', fn ($ano) => $caixa->ano = $ano);
        request()->whenHas('guarda_permanente', fn ($guarda_permanente) => $caixa->guarda_permanente = $guarda_permanente);
        request()->whenHas('complemento', fn ($complemento) => $caixa->complemento = $complemento);
        request()->whenHas('descricao', fn ($descricao) => $caixa->descricao = $descricao);
        request()->whenFilled('localidade_criadora_id', fn ($localidade_criadora_id) => $caixa->localidade_criadora_id = $localidade_criadora_id);

        $caixa->save();

        return $next($caixa);
    }
}
