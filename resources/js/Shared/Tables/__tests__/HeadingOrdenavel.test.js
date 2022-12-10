/**
 * Testes para o componente HeadingOrdenavel.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import HeadingOrdenavel from '@/Shared/Tables/HeadingOrdenavel.vue';
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
        return mount(HeadingOrdenavel, { ...options });
    };
});

// Caminho feliz
describe('HeadingOrdenavel', () => {
    test('propriedades do componente estão definidas', () => {
        expect(HeadingOrdenavel.props).toMatchObject({
            texto: { type: String, required: true },
            ordenacao: { type: String },
        });
    });

    const ordenacoes = [
        ['', 'asc'],
        ['asc', 'desc'],
        ['desc', undefined],
    ];
    test.each(ordenacoes)(
        'renderiza o componente com o ícone respectivo respeitando o snapshot',
        (ordenacao) => {
            expect(
                mountFunction({
                    props: { texto: 'foo', ordenacao: ordenacao },
                    global: {
                        plugins: [pinia({ processando: false })],
                    },
                }).html()
            ).toMatchSnapshot();
        }
    );

    test.each(ordenacoes)(
        'renderiza o componente de maneira diversa enquanto a página se carrega respeitando o snapshot',
        (entrada) => {
            expect(
                mountFunction({
                    props: { texto: 'foo', ordenacao: entrada },
                    global: {
                        plugins: [pinia({ processando: true })],
                    },
                }).html()
            ).toMatchSnapshot();
        }
    );

    test.each(ordenacoes)(
        'dispara o evento ordenar com o novo valor da ordenação ao clicar no componente',
        (entrada, saida) => {
            const wrapper = mountFunction({
                props: { texto: 'foo', ordenacao: entrada },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            });

            wrapper.vm.setOrdenacao();

            expect(wrapper.emitted()['ordenar'][0]).toEqual([saida]);
        }
    );
});
