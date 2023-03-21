/**
 * Testes para os helpers dos objetos.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { porGuia } from '@/Helpers/Solicitacao';
import { describe, expect, test } from 'vitest';

describe('Solicitacao', () => {
    const cases = [
        [true, 'sim'],
        [false, 'não'],
    ];

    test.each(cases)('retorna se a solicitação foi feita por guia', (por_guia, retorno) => {
        const solicitacao = {
            entregue_em: 'foo',
            por_guia: por_guia,
        };

        expect(porGuia(solicitacao)).toBe(retorno);
    });

    test.each(cases)(
        'retorna null indepdente do valor por guia, caso a solicitação não tenha sido entregue',
        (por_guia) => {
            const solicitacao = {
                entregue_em: null,
                por_guia: por_guia,
            };

            expect(porGuia(solicitacao)).toBeNull();
        }
    );
});
