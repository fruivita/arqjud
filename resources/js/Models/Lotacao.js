/**
 * Encapsulamento de funcionalidades relativas à lotação pra uso no client
 * side.
 */

class Lotacao {
    constructor(attributes = {}) {
        Object.assign(this, attributes);
    }

    /**
     * Nome da lotação para exibição.
     *
     * @return {String}
     */
    nomeExibicao() {
        return `${this.sigla} - ${this.nome}`;
    }
}

export default Lotacao;
