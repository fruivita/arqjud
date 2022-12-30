<?php

namespace App\Pipes\Usuario;

use App\Models\Usuario;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class AlterarPerfil
{
    /**
     * Altera por pipe o perfil do usuário.
     *
     * @param  \App\Models\Usuario  $usuario
     * @param  \Closure  $next
     * @param  int  $perfil
     * @return \App\Models\Usuario
     */
    public function handle(Usuario $usuario, \Closure $next, int $perfil)
    {
        $usuario->perfil_id = $perfil;
        $usuario->save();

        return $next($usuario);
    }
}
