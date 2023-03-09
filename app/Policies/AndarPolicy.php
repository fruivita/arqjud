<?php

namespace App\Policies;

use App\Models\Andar;
use App\Models\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class AndarPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::ANDAR_VIEW_ANY);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::ANDAR_VIEW);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::ANDAR_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::ANDAR_UPDATE);
    }

    /**
     * Determina se o usuário pode visualizar ou atualizar um modelo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewOrUpdate(Usuario $usuario)
    {
        return
            $this->view($usuario)
            || $this->update($usuario);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Usuario $usuario, Andar $andar)
    {
        if ($usuario->possuiPermissao(Permissao::ANDAR_DELETE) !== true) {
            return false;
        }

        if (isset($andar->salas_count) !== true) {
            $andar->loadCount('salas');
        }
        if ($andar->salas_count !== 0) {
            return false;
        }

        return true;
    }
}
