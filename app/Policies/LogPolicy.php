<?php

namespace App\Policies;

use App\Enums\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/authorization
 */
class LogPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar quaisquer arquivos de log.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::LogViewAny);
    }

    /**
     * Determinada se o usuário pode excluir arquivos de log.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::LogDelete);
    }

    /**
     * Determina se o usuário pode fazer o download de arquivos de log.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function download(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::LogDownload);
    }
}
