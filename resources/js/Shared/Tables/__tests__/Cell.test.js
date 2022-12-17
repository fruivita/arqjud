/**
 * Testes para o componente Cell.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Cell from '@/Shared/Tables/Cell.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Cell, { ...options });
    };
});

describe('Cell', () => {
    test('propriedades estão definidas', () => {
        expect(Cell.props).toMatchObject({
            erro: { type: String, default: '' },
            tooltip: { type: [Array, String] },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                slots: { default: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza a mensagem de erro respeitando o snapshot', () => {
        expect(
            mountFunction({
                slots: { default: 'foo' },
                props: { erro: 'bar' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente tooltip respeitando o snapshot', () => {
        expect(
            mountFunction({
                slots: { default: 'foo' },
                props: { erro: 'bar', tooltip: 'baz' },
            }).html()
        ).toMatchSnapshot();
    });
});
