<?php

namespace App\Pipes\Lotacao;

use App\Models\Lotacao;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class ToggleAdministravel
{
    /**
     * Altera o status administrável da lotação.
     *
     * @param  \App\Models\Lotacao  $lotacao
     * @param  \Closure  $next
     * @return \App\Models\Lotacao
     */
    public function handle(Lotacao $lotacao, \Closure $next)
    {
        $lotacao->administravel = !$lotacao->administravel;
        $lotacao->save();

        return $next($lotacao);
    }
}
