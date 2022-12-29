/**
 * Testes para o componente CheckBox.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import CheckBox from '@/Shared/Forms/CheckBox.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('@/Composables/UseGerarID', () => ({
    __esModule: true,
    ...vi.importActual('@/Composables/UseGerarID'),
    gerarID: () => 'foo-id',
}));

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(CheckBox, { ...options });
    };
});

describe('CheckBox', () => {
    test('propriedades estão definidas', () => {
        expect(CheckBox.props).toMatchObject({
            id: { type: String },
            label: { type: String, required: true },
            disabled: { type: Boolean, default: false },
            checked: { type: Boolean, required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { label: 'foo', checked: false },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente desabilitado respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { label: 'foo', disabled: true, checked: false },
            }).html()
        ).toMatchSnapshot();
    });

    test('atributos do componente e extras renderizados corretamente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    id: '123',
                    label: 'foo',
                    checked: true,
                    extra: 'loren',
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('dispara o evento onClick se o componente estiver habilitado', () => {
        const wrapper = mountFunction({
            props: { label: 'foo', disabled: false, checked: false },
        });

        const event = { target: { checked: true } };

        wrapper.vm.onClick(event);

        expect(wrapper.emitted()['update:checked'][0]).toEqual([true]);
    });

    test('não dispara o evento onClick se o componente estiver desabilitado', () => {
        const wrapper = mountFunction({
            props: { label: 'foo', disabled: true, checked: false },
        });

        const event = { target: { checked: true } };

        wrapper.vm.onClick(event);

        expect(wrapper.emitted()['update:checked']).toBeFalsy();
    });
});
