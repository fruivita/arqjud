/**
 * Testes para o componente ContadorCaracteres.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import ContadorCaracteres from '@/Shared/Forms/ContadorCaracteres.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(ContadorCaracteres, { ...options });
    };
});

// Caminho feliz
describe('ContadorCaracteres', () => {
    test('propriedades do componente estão definidas', () => {
        expect(ContadorCaracteres.props).toMatchObject({
            texto: { type: String, required: true },
            maxlength: { type: [Number, String], required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: 'foo', maxlength: 5 },
            }).html()
        ).toMatchSnapshot();
    });

    const cases = [
        ['', '0'],
        ['foo', '3'],
        ['foobar', '6'],
    ];

    test.each(cases)('exibe a quantidade de caracteres digitada', (entrada, saida) => {
        const wrapper = mountFunction({
            props: { texto: entrada, maxlength: 5 },
        });

        expect(wrapper.get('[dusk="maxlength"]').text()).toBe('5');
        expect(wrapper.get('[dusk="contador"]').text()).toBe(saida);
    });
});
