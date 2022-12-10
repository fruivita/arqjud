/**
 * Testes para o componente Tabela.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Tabela from '@/Shared/Tables/Tabela.vue';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;
let pinia;

beforeEach(() => {
    pinia = (options = {}) =>
        createTestingPinia({
            initialState: {
                StatusRequisicaoStore: { ...options },
            },
        });

    mountFunction = (options = {}) => {
        return mount(Tabela, { ...options });
    };
});

describe('Tabela', () => {
    // Caminho feliz
    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                slots: { header: 'foo', body: 'bar' },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente de maneira diversa enquanto a página se carrega respeitando o snapshot', () => {
        expect(
            mountFunction({
                slots: { header: 'foo', body: 'bar' },
                global: {
                    plugins: [pinia({ processando: true })],
                },
            }).html()
        ).toMatchSnapshot();
    });
});
