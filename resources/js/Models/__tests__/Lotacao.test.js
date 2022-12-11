/**
 * Testes para o modelo Lotação.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import Lotacao from '@/Models/Lotacao';
import { describe, expect, test } from 'vitest';

describe('Lotacao', () => {
    test('retorna o nome da lotação para exibição', () => {
        const lotacao = new Lotacao({
            sigla: 'foo',
            nome: 'bar',
        });

        expect(lotacao.nomeExibicao()).toBe('foo - bar');
    });
});
