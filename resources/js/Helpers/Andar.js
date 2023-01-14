/**
 * Helpers para manipulação de dados do objeto.
 */

function numeroAndar(andar) {
    let texto = `${andar.numero}`;

    if (andar.apelido) {
        texto = `${texto} (${andar.apelido})`;
    }

    return texto;
}
export { numeroAndar };
