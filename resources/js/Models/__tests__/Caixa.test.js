/**
 * Testes para o modelo Caixa.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import Caixa from '@/Models/Caixa';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, test } from 'vitest';

setActivePinia(createPinia());

describe('Caixa', () => {
    const __ = useTranslationsStore().__;

    test('retorna o nome da caixa completo', () => {
        const caixa = new Caixa({
            numero: 123,
            ano: 2000,
            guarda_permanente: true,
            localidade_criadora: { nome: 'bar' },
            complemento: 'foo',
        });

        expect(caixa.numeroExibicao()).toBe('123/2000/GP:Sim/bar/foo');
    });

    test('retorna o nome da caixa sem o complemento', () => {
        const caixa = new Caixa({
            numero: 123,
            ano: 2000,
            guarda_permanente: true,
            localidade_criadora: { nome: 'bar' },
        });

        expect(caixa.numeroExibicao()).toBe('123/2000/GP:Sim/bar');
    });

    test('retorna o nome da caixa sem o nome da localidade criadora', () => {
        const caixa = new Caixa({
            numero: 123,
            ano: 2000,
            guarda_permanente: true,
            complemento: 'foo',
        });

        expect(caixa.numeroExibicao()).toBe('123/2000/GP:Sim/foo');
    });

    test('retorna o texto se a caixa é de guarda_permanente', () => {
        const caixa = new Caixa({
            guarda_permanente: true,
        });

        expect(caixa.gp()).toBe(__('Sim'));
    });

    test('retorna o texto se a caixa não é de guarda_permanente', () => {
        const caixa = new Caixa({
            guarda_permanente: false,
        });

        expect(caixa.gp()).toBe(__('Não'));
    });
});
