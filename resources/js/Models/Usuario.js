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
}

export default Usuario;
