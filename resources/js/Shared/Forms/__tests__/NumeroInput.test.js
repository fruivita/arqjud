/**
 * Testes para o componente NumeroInput.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import NumeroInput from '@/Shared/Forms/NumeroInput.vue';
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
        return mount(NumeroInput, {
            props: { modelValue: 10, min: 5, max: 20, ...options },
        });
    };
});

describe('NumeroInput', () => {
    test('propriedades estão definidas', () => {
        expect(NumeroInput.props).toMatchObject({
            id: { type: String },
            icone: { type: String },
            erro: { type: String },
            label: { type: String },
            min: { type: [Number, String], required: true },
            max: { type: [Number, String], required: true },
            modelValue: { type: [Number, String] },
            disabled: { type: Boolean, default: false },
            required: { type: Boolean, default: false },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });

    test('renderiza o label respeitando o snapshot', () => {
        expect(mountFunction({ label: 'bar' }).html()).toMatchSnapshot();
    });

    test('renderiza o símbolo de campo obrigatório respeitando o snapshot', () => {
        expect(mountFunction({ label: 'bar', required: true }).html()).toMatchSnapshot();
    });

    test('renderiza o ícone respeitando o snapshot', () => {
        expect(mountFunction({ icone: 'key' }).html()).toMatchSnapshot();
    });

    test('renderiza a mensagem de erro respeitando o snapshot', () => {
        expect(mountFunction({ erro: 'bar' }).html()).toMatchSnapshot();
    });

    test('atributos do componente e extras renderizados corretamente respeitando o snapshot', () => {
        expect(mountFunction({ id: '123', extra: 'loren' }).html()).toMatchSnapshot();
    });

    test('rendiza o componente de maneira diversa quando desabilitado respeitando o snapshot', () => {
        expect(mountFunction({ disabled: true }).html()).toMatchSnapshot();
    });

    test('atualiza o v-model ao alterar o valor do elemento', () => {
        const parent = mount({
            data: () => {
                return { foo: 10 };
            },
            template: '<div> <NumeroInput v-model="foo" :min="5" :max="20"/> </div>',
            components: { NumeroInput: NumeroInput },
        });

        const input = parent.find('input');

        input.element.value = 15;
        input.trigger('input');

        expect(parent.vm.foo).toBe('15');
    });

    test('dispara o evento onInput se o componente estiver habilitado', () => {
        const wrapper = mountFunction();

        const event = { target: { value: 15 } };

        wrapper.vm.onInput(event);

        expect(wrapper.emitted()['update:modelValue'][0]).toEqual([15]);
    });

    test('não dispara o evento onClick se o componente estiver desabilitado', () => {
        const wrapper = mountFunction({ disabled: true });

        const event = { target: { value: 15 } };

        wrapper.vm.onInput(event);

        expect(wrapper.emitted()['update:modelValue']).toBeFalsy();
    });
});
