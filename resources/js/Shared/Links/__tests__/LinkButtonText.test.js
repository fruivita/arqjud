/**
 * Testes para o componente LinkButtonText.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import LinkButtonText from '@/Shared/Links/LinkButtonText.vue';
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
        return mount(LinkButtonText, { ...options });
    };
});

describe('LinkButtonText', () => {
    // Caminho feliz
    const especies = ['padrao', 'acao', 'inacao', 'alerta', 'perigo'];

    test('aceita apenas as espécies definidas', () => {
        const validator = LinkButtonText.props.especie.validator;

        especies.forEach((especie) => expect(validator(especie)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('propriedades do componente estão definidas', () => {
        expect(LinkButtonText.props).toMatchObject({
            icone: { type: String },
            href: { type: String, required: true },
            especie: { type: String, default: 'padrao' },
            texto: { type: String, required: true },
        });
    });

    test.each(especies)(
        'renderiza o componente das espécies específicas respeitando o snapshot',
        (especie) => {
            expect(
                mountFunction({
                    props: { href: 'http://foo.bar', texto: 'foo', especie: especie },
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
                props: { href: 'http://foo.bar', icone: 'key', texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: false })],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('rendiza o componente de maneira diversa enquanto a página se carrega respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { href: 'http://foo.bar', icone: 'key', texto: 'foo' },
                global: {
                    plugins: [pinia({ processando: true })],
                },
            }).html()
        ).toMatchSnapshot();
    });
});
