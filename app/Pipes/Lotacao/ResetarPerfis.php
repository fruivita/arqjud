<?php

namespace App\Pipes\Lotacao;

use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class ResetarPerfis
{
    /**
     * Reseta por pipe os perfis dos usuários da lotação.
     *
     * O reset do perfil significa atribuir o perfil padrão (perfil de menor
     * poder) ao usuário.
     *
     * Regra de negócio: Se a lotação for administrável, os perfis podem ser
     * mantidos (provavelmente todos já serão perfil padrão). Contudo, se ela
     * não for administravel, todos os perfis devem ser resetados.
     *
     * @param  \App\Models\Lotacao  $lotacao
     * @param  \Closure  $next
     * @return \App\Models\Lotacao
     */
    public function handle(Lotacao $lotacao, \Closure $next)
    {
        if ($lotacao->administravel !== true) {
            $perfis = Perfil::whereIn('slug', [Perfil::ADMINISTRADOR, Perfil::PADRAO])->get();

            Usuario::query()
                ->whereBelongsTo($lotacao, 'lotacao')
                ->whereNot('perfil_id', $perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id)
                ->update([
                    'perfil_id' => $perfis->firstWhere('slug', Perfil::PADRAO)->id,
                    'perfil_concedido_por' => null,
                    'antigo_perfil_id' => null,
                ]);
        }

        return $next($lotacao);
    }
}
