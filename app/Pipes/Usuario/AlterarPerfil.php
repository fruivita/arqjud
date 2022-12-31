<?php

namespace App\Pipes\Usuario;

use App\Models\Perfil;
use App\Models\Usuario;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class AlterarPerfil
{
    /**
     * Altera por pipe o perfil do usuário.
     *
     * Lança exception se o usuário autenticado não possuir poder suficiente
     * para alterar o perfil do usuário.
     *
     * Regra de negócio: Usuário autenticado tem que possuir perfil igual ou
     * superior ao perfil do usuário em edição e o perfil de final.
     *
     * @param  \App\Models\Usuario  $usuario
     * @param  \Closure  $next
     * @param  int  $perfil id do perfil final do usuário
     * @return \App\Models\Usuario
     *
     * @throws \RuntimeException
     */
    public function handle(Usuario $usuario, \Closure $next, int $perfil)
    {
        $perfil_inicial = Perfil::find($usuario->perfil_id);
        $perfil_final = Perfil::find($perfil);
        $perfil_autenticado = Perfil::find(auth()->user()->perfil_id);

        throw_if(
            $perfil_autenticado->poder < $perfil_inicial->poder
                || $perfil_autenticado->poder < $perfil_final->poder,
            \RuntimeException::class,
            __('Tentativa de alteração de perfil superior')
        );

        $usuario->perfil_id = $perfil;
        $usuario->save();

        return $next($usuario);
    }
}
