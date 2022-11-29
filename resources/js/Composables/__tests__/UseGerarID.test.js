/**
 * Testes para o composable useGerarID.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { gerarID } from '@/Composables/UseGerarID';
import { describe, expect, test } from 'vitest';

// Caminho feliz
describe('useGerarID', () => {
    const padrao = '[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}';

    test('gera o id no formato UUID', () => {
        const regex = new RegExp(padrao);

        expect(regex.test(gerarID())).toBe(true);
    });
});
