/**
 * Testes para o componente Tooltip.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Tooltip from '@/Shared/Misc/Tooltip.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Tooltip, { ...options });
    };
});

describe('Tooltip', () => {
    // Caminho feliz
    const posicoes = ['bottom', 'top', 'left', 'right'];

    test('aceita apenas as posições definidas', () => {
        const validator = Tooltip.props.posicao.validator;

        posicoes.forEach((type) => expect(validator(type)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('propriedades do componente estão definidas', () => {
        expect(Tooltip.props).toMatchObject({
            icone: { type: String, default: 'info-circle' },
            texto: { type: [Array, String], required: true },
            posicao: { type: String, default: 'bottom' },
        });
    });

    test('renderiza o componente default respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente com ícone específico respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente informando um array de textos respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: ['foo', 'bar', 'baz'] },
            }).html()
        ).toMatchSnapshot();
    });

    test.each(posicoes)(
        'renderiza o componente nas posições específicas respeitando o snapshot',
        (posicao) => {
            expect(
                mountFunction({
                    props: { texto: 'foo', posicao: posicao },
                }).html()
            ).toMatchSnapshot();
        }
    );

    test('exibe o tooltip ao clicar no botão respeitando o snapshot', async () => {
        const wrapper = mountFunction({
            props: { texto: 'foo' },
        });

        await wrapper.find('[dusk="toggle"]').trigger('click');

        expect(wrapper.html()).toMatchSnapshot();
    });

    test('exibe o tooltip ao clicar no botão', async () => {
        const wrapper = mountFunction({
            props: { texto: 'foo' },
        });

        const tooltip = wrapper.get('[dusk="tooltip"]');

        expect(tooltip.isVisible()).toBeFalsy();

        await wrapper.get('[dusk="toggle"]').trigger('click');

        expect(tooltip.isVisible()).toBeTruthy();
    });

    test('alterna a visibilidade do tooltip nos ventos mouseover e mouseout no botão', async () => {
        const wrapper = mountFunction({
            props: { texto: 'foo' },
        });

        const tooltip = wrapper.get('[dusk="tooltip"]');

        expect(tooltip.isVisible()).toBeFalsy();

        await wrapper.get('[dusk="toggle"]').trigger('mouseover');

        expect(tooltip.isVisible()).toBeTruthy();

        await wrapper.get('[dusk="toggle"]').trigger('mouseout');

        expect(tooltip.isVisible()).toBeFalsy();
    });

    test('mouseover e mouseout não possuem efeito se houver um click para exibição', async () => {
        const wrapper = mountFunction({
            props: { texto: 'foo' },
        });

        const tooltip = wrapper.get('[dusk="tooltip"]');

        expect(tooltip.isVisible()).toBeFalsy();

        await wrapper.get('[dusk="toggle"]').trigger('click');

        expect(tooltip.isVisible()).toBeTruthy();

        await wrapper.get('[dusk="toggle"]').trigger('mouseover');

        expect(tooltip.isVisible()).toBeTruthy();

        await wrapper.get('[dusk="toggle"]').trigger('mouseout');

        expect(tooltip.isVisible()).toBeTruthy();
    });
});
