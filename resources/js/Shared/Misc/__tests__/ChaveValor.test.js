/**
 * Testes para o componente ChaveValor.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(ChaveValor, { ...options });
    };
});

describe('ChaveValor', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(ChaveValor.props).toMatchObject({
            icone: { type: String },
            chave: { type: String, required: true },
            valor: { type: [Number, String] },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { chave: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente com o valor informado respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { chave: 'foo', valor: 'bar' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente com o ícone respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', chave: 'foo', valor: 'bar' },
            }).html()
        ).toMatchSnapshot();
    });
});
