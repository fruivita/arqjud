/**
 * Testes para o componente InertiaButtonIconeLink.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
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
        return mount(InertiaButtonIconeLink, { ...options });
    };
});

describe('InertiaButtonIconeLink', () => {
    test('propriedades estão definidas', () => {
        expect(InertiaButtonIconeLink.props).toMatchObject({
            icone: { type: String, required: true },
            href: { type: String, required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', href: 'http://foo.bar' },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente de maneira diversa enquanto a página se carrega respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', href: 'http://foo.bar' },
                global: {
                    plugins: [pinia({ processando: true })],
                },
            }).html()
        ).toMatchSnapshot();
    });
});
