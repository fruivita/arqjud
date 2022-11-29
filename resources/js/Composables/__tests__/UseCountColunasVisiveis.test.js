/**
 * Testes para o composable useCountColunasVisiveis.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { countColunasVisiveis } from '@/Composables/UseCountColunasVisiveis';
import { describe, expect, test } from 'vitest';

describe('useCountColunasVisiveis', () => {
    test('contabiliza a quantidade de colunas visíveis', () => {
        expect(
            countColunasVisiveis({
                value: {
                    coluna1: true,
                    coluna2: false,
                    coluna3: true,
                    coluna4: true,
                },
            })
        ).toBe(3);
    });
});
