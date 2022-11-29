/**
 * Testes para o componente ModalBase.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import { exibirModalKey, fecharModalKey } from '@/keys.js';
import ModalBase from '@/Shared/Modals/ModalBase.vue';
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
        return mount(ModalBase, { ...options });
    };
});

describe('ModalBase', () => {
    const opcoes = [true, false];

    test.each(opcoes)(
        'renderiza e exibe o componente de acordo com o valor provido pelo componente pai',
        (opcao) => {
            expect(
                mountFunction({
                    global: {
                        provide: {
                            [exibirModalKey]: opcao,
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
        }
    );
});
