/**
 * Testes para o componente ButtonIcone.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(ButtonIcone, { ...options });
    };
});

describe('ButtonIcone', () => {
    // Caminho feliz
    const types = ['button', 'submit'];
    const especies = ['acao', 'alerta', 'perigo'];

    test('aceita apenas os types definidos', () => {
        const validator = ButtonIcone.props.type.validator;

        types.forEach((type) => expect(validator(type)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('aceita apenas as espécies definidas', () => {
        const validator = ButtonIcone.props.especie.validator;

        especies.forEach((especie) => expect(validator(especie)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('propriedades do componente estão definidas', () => {
        expect(ButtonIcone.props).toMatchObject({
            icone: { type: String, required: true },
            especie: { type: String, default: 'acao' },
            type: { type: String, default: 'button' },
        });
    });

    test.each(types)(
        'renderiza o componente com os types específicos respeitando o snapshot',
        (type) => {
            expect(
                mountFunction({
                    props: { icone: 'key', type: type },
                }).html()
            ).toMatchSnapshot();
        }
    );

    test.each(especies)(
        'renderiza o componente das espécies específicas respeitando o snapshot',
        (especie) => {
            expect(
                mountFunction({
                    props: { icone: 'key', especie: especie },
                }).html()
            ).toMatchSnapshot();
        }
    );
});
