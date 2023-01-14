/**
 * Testes para os helpers dos objetos.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { gp, numeroCaixa } from '@/Helpers/Caixa';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, test } from 'vitest';

setActivePinia(createPinia());

const __ = useTranslationsStore().__;

describe('Caixa', () => {
    test('retorna o nome da caixa completo', () => {
        const caixa = {
            numero: 123,
            ano: 2000,
            guarda_permanente: true,
            localidade_criadora: { nome: 'bar' },
            complemento: 'foo',
        };

        expect(numeroCaixa(caixa)).toBe('123/2000/GP:Sim/bar/foo');
    });

    test('retorna o nome da caixa sem o complemento', () => {
        const caixa = {
            numero: 123,
            ano: 2000,
            guarda_permanente: true,
            localidade_criadora: { nome: 'bar' },
        };

        expect(numeroCaixa(caixa)).toBe('123/2000/GP:Sim/bar');
    });

    test('retorna o nome da caixa sem o nome da localidade criadora', () => {
        const caixa = {
            numero: 123,
            ano: 2000,
            guarda_permanente: true,
            complemento: 'foo',
        };

        expect(numeroCaixa(caixa)).toBe('123/2000/GP:Sim/foo');
    });

    test('retorna o texto se a caixa é de guarda_permanente', () => {
        const caixa = { guarda_permanente: true };

        expect(gp(caixa)).toBe(__('Sim'));
    });

    test('retorna o texto se a caixa não é de guarda_permanente', () => {
        const caixa = { guarda_permanente: false };

        expect(gp(caixa)).toBe(__('Não'));
    });
});
