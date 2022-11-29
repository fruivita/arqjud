/**
 * Testes para o componente MenuLink.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import MenuLink from '@/Shared/Menus/MenuLink.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(MenuLink, { ...options });
    };
});

describe('MenuLink', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(MenuLink.props).toMatchObject({
            ativo: { type: Boolean, default: false },
            icone: { type: String },
            texto: { type: String, required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o ícone respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente sem estar no estado ativo (não selecionado) respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { ativo: false, texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente com o estado ativo (selecionado) respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { ativo: true, texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });
});
