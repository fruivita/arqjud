/**
 * Encapsulamento de funcionalidades relativas ao usuário pra uso no client
 * side.
 */

import { isEmpty } from 'lodash';

class Usuario {
    constructor(attributes = {}) {
        Object.assign(this, attributes);
    }

    /**
     * Nome do usuário para exibição.
     *
     * @return {String}
     */
    nomeExibicao() {
        return isEmpty(this.nome) ? this.username : this.nome;
    }

    /**
     * Dados sobre o último login para exibição.
     *
     * @return {String}
     */
    ultimoLogin() {
        let mensagem = '';

        if (this.ultimo_login) {
            mensagem = this.ultimo_login;
        }

        if (this.ip) {
            mensagem = mensagem ? `${mensagem} (${this.ip})` : this.ip;
        }

        return mensagem;
    }
}

export default Usuario;
