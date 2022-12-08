/**
 * Encapsulamento de funcionalidades relativas ao andar pra uso no client side.
 */

class Andar {
    constructor(attributes = {}) {
        Object.assign(this, attributes);
    }

    /**
     * Número completo do andar para exibição.
     *
     * Ex.: 10 (Garagem)
     *
     * @return {String}
     */
    numeroExibicao() {
        let texto = `${this.numero}`;

        if (this.apelido) {
            texto = `${texto} (${this.apelido})`;
        }

        return texto;
    }
}

export default Andar;
