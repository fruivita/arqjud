/**
 * Testes para o componente ModalConfirmacao.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import { exibirModalKey, fecharModalKey } from '@/keys.js';
import ModalConfirmacao from '@/Shared/Modals/ModalConfirmacao.vue';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

let mountFunction;
let pinia;

beforeEach(() => {
    pinia = (options = {}) =>
        createTestingPinia({
            stubActions: false,
            initialState: {
                StatusRequisicaoStore: { ...options },
            },
        });

    mountFunction = (options = {}) => {
        return mount(ModalConfirmacao, { ...options });
    };
});

describe('ModalConfirmacao', () => {
    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                global: {
                    provide: {
                        [exibirModalKey]: true,
                        [fecharModalKey]: vi.fn(),
                    },
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza todos os slots do componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                global: {
                    provide: {
                        [exibirModalKey]: true,
                        [fecharModalKey]: vi.fn(),
                    },
                    plugins: [pinia({ processando: false })],
                },
                slots: {
                    header: 'foo',
                    body: 'bar',
                    footer: 'baz',
                },
            }).html()
        ).toMatchSnapshot();
    });
});
