/**
 * Testes para o componente Hint.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Hint from '@/Shared/Misc/Hint.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Hint, { ...options });
    };
});

describe('Hint', () => {
    // Caminho feliz
    const posicoes = ['bottom', 'top', 'left', 'right'];

    test('aceita apenas as posições definidas', () => {
        const validator = Hint.props.posicao.validator;

        posicoes.forEach((type) => expect(validator(type)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('propriedades do componente estão definidas', () => {
        expect(Hint.props).toMatchObject({
            texto: { type: [Array, String], required: true },
            posicao: { type: String, default: 'bottom' },
        });
    });

    test('renderiza o componente default respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: 'foo' },
                slots: { default: 'loren' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente informando um array de textos respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: ['foo', 'bar', 'baz'] },
                slots: { default: 'loren' },
            }).html()
        ).toMatchSnapshot();
    });

    test.each(posicoes)(
        'renderiza o componente nas posições específicas respeitando o snapshot',
        (posicao) => {
            expect(
                mountFunction({
                    props: { texto: 'foo', posicao: posicao },
                    slots: { default: 'loren' },
                }).html()
            ).toMatchSnapshot();
        }
    );

    test('alterna a visibilidade do hint nos eventos mouseover e mouseout no botão', async () => {
        const wrapper = mountFunction({
            props: { texto: 'foo' },
        });

        const hint = wrapper.get('[dusk="hint"]');

        expect(hint.isVisible()).toBeFalsy();

        await wrapper.get('[dusk="toggle"]').trigger('mouseover');

        expect(hint.isVisible()).toBeTruthy();

        await wrapper.get('[dusk="toggle"]').trigger('mouseout');

        expect(hint.isVisible()).toBeFalsy();
    });
});
