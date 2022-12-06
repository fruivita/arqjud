/**
 * Testes para o composable useCountElementosVisiveis.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import Caixa from '@/Models/Caixa';
import { expect, test } from 'vitest';

describe('Caixa', () => {
    test('retorna o nome da caixa completo', () => {
        const caixa = new Caixa({
            numero: 123,
            ano: 2000,
            guarda_permanente: 'Sim',
            complemento: 'foo',
            localidade_criadora: { nome: 'bar' },
        });

        expect(caixa.numeroExibicao()).toBe('123/2000/GP:Sim/foo/bar');
    });

    test('retorna o nome da caixa sem o complemento', () => {
        const caixa = new Caixa({
            numero: 123,
            ano: 2000,
            guarda_permanente: 'Sim',
            localidade_criadora: { nome: 'bar' },
        });

        expect(caixa.numeroExibicao()).toBe('123/2000/GP:Sim/bar');
    });

    test('retorna o nome da caixa sem o nome da localidade criadora', () => {
        const caixa = new Caixa({
            numero: 123,
            ano: 2000,
            guarda_permanente: 'Sim',
            complemento: 'foo',
        });

        expect(caixa.numeroExibicao()).toBe('123/2000/GP:Sim/foo');
    });
});
