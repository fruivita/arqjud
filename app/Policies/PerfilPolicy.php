<?php

namespace App\Policies;

use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class PerfilPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::PERFIL_VIEW_ANY);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::PERFIL_VIEW);
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::PERFIL_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Usuario $usuario)
    {
        return $usuario->possuiPermissao(Permissao::PERFIL_UPDATE);
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
     * Regra de negócio: perfis originais não podem ser excluídos:
     * - Administrador;
     * - Gestor de Negócio;
     * - Operador;
     * - Observador;
     * - Padrão.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Usuario $usuario, Perfil $perfil)
    {
        if (
            in_array($perfil->slug, [
                Perfil::ADMINISTRADOR,
                Perfil::GERENTE_NEGOCIO,
                Perfil::OPERADOR,
                Perfil::OBSERVADOR,
                Perfil::PADRAO,
            ])
            || $usuario->possuiPermissao(Permissao::PERFIL_DELETE) !== true
        ) {
            return false;
        }

        if (isset($perfil->usuarios_count) !== true) {
            $perfil->loadCount('usuarios');
        }
        if ($perfil->usuarios_count !== 0) {
            return false;
        }

        return true;
    }
}
