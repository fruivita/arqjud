/**
 * Testes para o composable useCountElementosVisiveis.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { describe, expect, test } from 'vitest';

describe('useCountElementosVisiveis', () => {
    test('contabiliza a quantidade de elementos ocultáveis visíveis', () => {
        expect(
            countElementosVisiveis({
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
