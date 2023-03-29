/**
 * Testes para o componente MenuGrupo.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import MenuGrupo from '@/Shared/Menus/MenuGrupo.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(MenuGrupo, { ...options });
    };
});

describe('MenuGrupo', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(MenuGrupo.props).toMatchObject({
            ativo: { type: Boolean, default: false },
            texto: { type: String, required: true },
        });
    });

    test('atributos e slots do componente renderizados respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: 'foo' },
                slots: { default: 'bar' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente sem estar no estado ativo (não selecionado) respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { ativo: false, texto: 'foo' },
                slots: { default: 'bar' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente com o estado ativo (selecionado) respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { ativo: true, texto: 'foo' },
                slots: { default: 'bar' },
            }).html()
        ).toMatchSnapshot();
    });

    test('altera a visibilidade do slot e disposição do ícone ao clicar no botão respeitando o snapshot', async () => {
        const wrapper = mountFunction({
            props: { texto: 'foo' },
            slots: { default: 'bar' },
        });

        expect(wrapper.get('[dusk="slot"]').isVisible()).toBeFalsy();
        expect(wrapper.html()).toMatchSnapshot();

        await wrapper.get('[dusk="btn-menu-grupo"]').trigger('click');

        expect(wrapper.get('[dusk="slot"]').isVisible()).toBeTruthy();
        expect(wrapper.html()).toMatchSnapshot();
    });
});
