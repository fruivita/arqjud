/**
 * Testes para o composable UseOrdenacao.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { describe, expect, test } from 'vitest';

// Caminho feliz
describe('useUseOrdenacao', () => {
    test('define a ordenação como esperado', () => {
        const { ordenacoes, mudarOrdenacao } = useOrdenacao({});
        mudarOrdenacao('coluna1', 'asc');
        expect(ordenacoes.value.coluna1).toBe('asc');
        mudarOrdenacao('coluna1', 'desc');
        expect(ordenacoes.value.coluna1).toBe('desc');
        mudarOrdenacao('coluna1', '');
        expect(ordenacoes.value.coluna1).toBe(undefined);
    });
});
