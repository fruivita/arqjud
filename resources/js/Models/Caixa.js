/**
 * Encapsulamento de funcionalidades relativas caixa pra uso no client side.
 */
class Caixa {
    constructor(attributes = {}) {
        Object.assign(this, attributes);
    }

    /**
     * Nome completo da caixa para exibição.
     *
     * @return string
     */
    numeroExibicao() {
        let texto = `${this.numero}/${this.ano}/GP:${this.guarda_permanente}`;
        if (this.complemento) {
            texto = `${texto}/${this.complemento}`;
        }
        if (this.localidade_criadora) {
            texto = `${texto}/${this.localidade_criadora.nome}`;
        }

        return texto;
    }
}

export default Caixa;
