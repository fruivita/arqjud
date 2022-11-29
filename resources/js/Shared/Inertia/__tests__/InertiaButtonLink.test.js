/**
 * Testes para o componente InertiaButtonLink.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import InertiaButtonLink from '@/Shared/Inertia/InertiaButtonLink.vue';
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
        return mount(InertiaButtonLink, { ...options });
    };
});

describe('InertiaButtonLink', () => {
    test('propriedades estão definidas', () => {
        expect(InertiaButtonLink.props).toMatchObject({
            icone: { type: String },
            href: { type: String, required: true },
            texto: { type: String, required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { href: 'http://foo.bar', texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente com ícone respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', href: 'http://foo.bar', texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('rendiza o componente de maneira diversa enquanto a página se carrega respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { href: 'http://foo.bar', texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: true })],
                },
            }).html()
        ).toMatchSnapshot();
    });
});
