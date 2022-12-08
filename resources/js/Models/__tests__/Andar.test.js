/**
 * Testes para o modelo Andar.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import Andar from '@/Models/Andar';
import { describe, expect, test } from 'vitest';

describe('Andar', () => {
    test('retorna o nome do andar completo', () => {
        const andar = new Andar({
            numero: 123,
            apelido: 'foo',
        });

        expect(andar.numeroExibicao()).toBe('123 (foo)');
    });

    test('retorna o nome do andar sem o apelido', () => {
        const andar = new Andar({
            numero: 123,
        });

        expect(andar.numeroExibicao()).toBe('123');
    });
});
