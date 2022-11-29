/**
 * Testes para o componente DropDown.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import DropDown from '@/Shared/Forms/DropDown.vue';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('@/Composables/UseGerarID', () => ({
    __esModule: true,
    ...vi.importActual('@/Composables/UseGerarID'),
    gerarID: () => 'foo-id',
}));

let mountFunction;

beforeEach(() => {
    const pinia = () => createTestingPinia({ stubActions: false });

    mountFunction = (options = {}) => {
        return mount(DropDown, {
            global: {
                plugins: [pinia()],
            },
            ...options,
        });
    };
});

describe('DropDown', () => {
    test('propriedades estão definidas', () => {
        expect(DropDown.props).toMatchObject({
            opcoes: { type: Array, required: true },
            id: { type: String },
            icone: { type: String },
            erro: { type: String },
            label: { type: String },
            modelValue: { type: [Number, String] },
            labelOpcao: { type: [Array, String], default: 'nome' },
            disabled: { type: Boolean, default: false },
            required: { type: Boolean, default: false },
        });
    });

    test('renderiza o label respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    label: 'loren',
                    modelValue: 2,
                    opcoes: [
                        { id: 1, nome: 'bar' },
                        { id: 2, nome: 'foo' },
                    ],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o símbolo de campo obrigatório respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    label: 'loren',
                    modelValue: 2,
                    opcoes: [
                        { id: 1, nome: 'bar' },
                        { id: 2, nome: 'foo' },
                    ],
                    required: true,
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o ícone respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    icone: 'key',
                    modelValue: 2,
                    opcoes: [
                        { id: 1, nome: 'bar' },
                        { id: 2, nome: 'foo' },
                    ],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza a mensagem de erro respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    modelValue: 2,
                    opcoes: [
                        { id: 1, nome: 'bar' },
                        { id: 2, nome: 'foo' },
                    ],
                    erro: 'loren',
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza a propriedade escolhida do objeto respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    modelValue: undefined,
                    labelOpcao: 'extra',
                    opcoes: [
                        { id: 1, nome: 'bar', extra: 'loren' },
                        { id: 2, nome: 'foo', extra: 'ipsun' },
                    ],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza múltiplas propriedades do objeto respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    modelValue: undefined,
                    opcoes: [
                        { id: 1, extra: 'bar', outra: 'loren' },
                        { id: 2, extra: 'foo', outra: 'ipsun' },
                    ],
                    labelOpcao: ['extra', 'outra'],
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('atributos do componente e extras renderizados corretamente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    modelValue: 2,
                    opcoes: [
                        { id: 1, nome: 'bar' },
                        { id: 2, nome: 'foo' },
                    ],
                    extra: 'loren',
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('rendiza o componente de maneira diversa quando desabilitado respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    modelValue: 2,
                    opcoes: [
                        { id: 1, nome: 'bar' },
                        { id: 2, nome: 'foo' },
                    ],
                    disabled: true,
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('rendiza o componente quando não há opções para o select respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    modelValue: undefined,
                    opcoes: [],
                    disabled: true,
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('atualiza o v-model ao alterar o valor do elemento', async () => {
        const parent = mount({
            data: () => {
                return {
                    selected: 2,
                    opcoes: [
                        { id: 1, nome: 'bar' },
                        { id: 2, nome: 'foo' },
                    ],
                };
            },
            template:
                '<div> <DropDown v-model="selected" :opcoes="opcoes" :maxlength="10"/> </div>',
            components: { DropDown: DropDown },
        });

        const dropdown = parent.find('select');

        dropdown.element.value = 1;
        await dropdown.trigger('change');

        expect(parent.vm.selected).toBe('1');
    });

    test('dispara o evento onChange se o componente estiver habilitado', () => {
        const wrapper = mountFunction({
            props: {
                modelValue: 2,
                maxlength: 10,
                opcoes: [
                    { id: 1, nome: 'bar' },
                    { id: 2, nome: 'foo' },
                ],
            },
        });

        const event = { target: { value: 1 } };

        wrapper.vm.onChange(event);

        expect(wrapper.emitted()['update:modelValue'][0]).toEqual([1]);
    });

    test('não dispara o evento onChange se o componente estiver desabilitado', () => {
        const wrapper = mountFunction({
            props: {
                modelValue: 2,
                maxlength: 10,
                opcoes: [
                    { id: 1, nome: 'bar' },
                    { id: 2, nome: 'foo' },
                ],
                disabled: true,
            },
        });

        const event = { target: { checked: true } };

        wrapper.vm.onChange(event);

        expect(wrapper.emitted()['update:modelValue']).toBeFalsy();
    });
});
