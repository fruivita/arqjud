/**
 * Testes para Pinia Store DadosEstaticosStore.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 * @see https://pinia.vuejs.org/cookbook/testing.html#unit-testing-a-store
 */

import { useDadosEstaticosStore } from '@/Stores/DadosEstaticosStore';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.stubGlobal('_dados', {
    app_nome: 'Foo',
    app_nome_completo: 'Foo Bar',
    app_versao: '1.0.0',
    orgao_sigla: 'BAZ',
    paginacao: [10, 20, 30],
});

beforeEach(() => {
    setActivePinia(createPinia());
});

// Caminho feliz
describe('DadosEstaticosStore', () => {
    test('store disponibiliza os dados corretamente', () => {
        const DadosEstaticosStore = useDadosEstaticosStore();

        expect(DadosEstaticosStore.appNome).toBe('Foo');
        expect(DadosEstaticosStore.appNomeCompleto).toBe('Foo Bar');
        expect(DadosEstaticosStore.appVersao).toBe('1.0.0');
        expect(DadosEstaticosStore.orgaoSigla).toBe('BAZ');
        expect(DadosEstaticosStore.paginacao).toStrictEqual([10, 20, 30]);
    });
});
