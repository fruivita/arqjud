/**
 * Testes para os helpers dos objetos.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { eAdministravel, nomeLotacao } from '@/Helpers/Lotacao';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, test } from 'vitest';

setActivePinia(createPinia());

const __ = useTranslationsStore().__;

describe('Lotação', () => {
    test('retorna o nome da lotação para exibição', () => {
        const lotacao = {
            sigla: 'foo',
            nome: 'bar',
        };

        expect(nomeLotacao(lotacao)).toBe('foo - bar');
    });

    test('retorna o nome da lotação para exibição sem o nome', () => {
        const lotacao = { sigla: 'foo' };

        expect(nomeLotacao(lotacao)).toBe('foo');
    });

    test('retorna o nome da lotação para exibição sem a sigla', () => {
        const lotacao = { nome: 'bar' };

        expect(nomeLotacao(lotacao)).toBe('bar');
    });

    test('retorna o nome da lotação vazio se não houver nome nem sigla', () => {
        const lotacao = {};

        expect(nomeLotacao(lotacao)).toBe('');
    });

    test('retorna se a lotação é administrável', () => {
        const lotacao = { administravel: true };

        expect(eAdministravel(lotacao)).toBe(__('Sim'));
    });

    test('retorna se a lotação não é administrável', () => {
        const lotacao = { administravel: false };

        expect(eAdministravel(lotacao)).toBe(__('Não'));
    });
});
