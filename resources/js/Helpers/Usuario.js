/**
 * Helpers para manipulação de dados do objeto.
 */

import { isEmpty } from 'lodash';

function nomeUsuario(usuario) {
    return isEmpty(usuario.nome) ? usuario.matricula : usuario.nome;
}

function ultimoLogin(usuario) {
    let texto = '';

    if (usuario.ultimo_login) {
        texto = usuario.ultimo_login;
    }

    if (usuario.ip) {
        texto = texto ? `${texto} (${usuario.ip})` : usuario.ip;
    }

    return texto;
}

export { nomeUsuario, ultimoLogin };
