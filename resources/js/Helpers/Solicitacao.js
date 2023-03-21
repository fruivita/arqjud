/**
 * Helpers para manipulação de dados do objeto.
 */

import { isEmpty } from 'lodash';

function porGuia(solicitacao) {
    let texto = solicitacao.por_guia ? 'sim' : 'não';

    return isEmpty(solicitacao.entregue_em) ? null : texto;
}

export { porGuia };
