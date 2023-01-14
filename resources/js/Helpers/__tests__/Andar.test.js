/**
 * Testes para os helpers dos objetos.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { numeroAndar } from '@/Helpers/Andar';
import { describe, expect, test } from 'vitest';

describe('Andar', () => {
    test('retorna o nome do andar completo', () => {
        const andar = {
            numero: 123,
            apelido: 'foo',
        };

        expect(numeroAndar(andar)).toBe('123 (foo)');
    });

    test('retorna o nome do andar sem o apelido', () => {
        const andar = { numero: 123 };

        expect(numeroAndar(andar)).toBe('123');
    });
});
