/**
 * Testes para o componente ButtonText.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import ButtonText from '@/Shared/Buttons/ButtonText.vue';
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
        return mount(ButtonText, { ...options });
    };
});

describe('ButtonText', () => {
    // Caminho feliz
    const types = ['button', 'submit'];
    const especies = ['padrao', 'acao', 'inacao', 'alerta', 'perigo'];

    test('aceita apenas os types definidos', () => {
        const validator = ButtonText.props.type.validator;

        types.forEach((type) => expect(validator(type)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('aceita apenas as espécies definidas', () => {
        const validator = ButtonText.props.especie.validator;

        especies.forEach((especie) => expect(validator(especie)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('propriedades do componente estão definidas', () => {
        expect(ButtonText.props).toMatchObject({
            icone: { type: String },
            especie: { type: String, default: 'padrao' },
            texto: { type: String, required: true },
            type: { type: String, default: 'button' },
        });
    });

    test.each(types)(
        'renderiza o componente com os types específicos respeitando o snapshot',
        (type) => {
            expect(
                mountFunction({
                    props: { texto: 'foo', type: type },
                    global: {
                        plugins: [pinia({ processando: false })],
                    },
                }).html()
            ).toMatchSnapshot();
        }
    );

    test.each(especies)(
        'renderiza o componente das espécies específicas respeitando o snapshot',
        (especie) => {
            expect(
                mountFunction({
                    props: { texto: 'foo', especie: especie },
                    global: {
                        plugins: [pinia({ processando: false })],
                    },
                }).html()
            ).toMatchSnapshot();
        }
    );

    test('renderiza o componente sem ícone respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente com ícone respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('rendiza o componente de maneira diversa enquanto a página se carrega respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: true })],
                },
            }).html()
        ).toMatchSnapshot();
    });
});
