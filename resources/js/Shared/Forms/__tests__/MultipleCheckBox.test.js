/**
 * Testes para o componente MultipleCheckBox.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import MultipleCheckBox from '@/Shared/Forms/MultipleCheckBox.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(MultipleCheckBox, { ...options });
    };
});

describe('MultipleCheckBox', () => {
    test('propriedades estão definidas', () => {
        expect(MultipleCheckBox.props).toMatchObject({
            disabled: { type: Boolean, default: false },
            value: { type: Array, required: true },
            opcoes: { type: Array, required: true },
        });
    });

    test('opções devem possuir chave nome e id', () => {
        const validator = MultipleCheckBox.props.opcoes.validator;

        expect(validator([{ foo: 1, loren: 'foo' }])).toBe(false);
        expect(validator([{ id: 1, nome: 'foo' }])).toBe(true);
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    value: [],
                    opcoes: [
                        { id: 1, nome: 'foo' },
                        { id: 2, nome: 'bar' },
                        { id: 3, nome: 'baz' },
                    ],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente desabilitado respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    disabled: true,
                    value: [],
                    opcoes: [
                        { id: 1, nome: 'foo' },
                        { id: 2, nome: 'bar' },
                        { id: 3, nome: 'baz' },
                    ],
                },
            }).html()
        ).toMatchSnapshot();
    });
});
