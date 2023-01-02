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
        let exibir = '';

        if (this.sigla) {
            exibir = this.sigla;
        }

        if (this.nome) {
            exibir = exibir ? `${exibir} - ${this.nome}` : this.nome;
        }

        return exibir;
    }
}

export default Lotacao;
