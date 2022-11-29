/**
 * Testes para o componente TextInput.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import TextInput from '@/Shared/Forms/TextInput.vue';
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
        return mount(TextInput, { ...options });
    };
});

describe('TextInput', () => {
    test('aceita apenas type text e password', () => {
        const types = ['text', 'password', 'email'];
        const validator = TextInput.props.type.validator;

        types.forEach((type) => expect(validator(type)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('propriedades estão definidas', () => {
        expect(TextInput.props).toMatchObject({
            id: { type: String },
            icone: { type: String },
            erro: { type: String },
            label: { type: String },
            maxlength: { type: [Number, String], required: true },
            modelValue: { type: String },
            disabled: { type: Boolean, default: false },
            required: { type: Boolean, default: false },
            type: { type: String, default: 'text' },
        });
    });

    test('renderiza o label respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { label: 'bar', modelValue: 'foo', maxlength: 10 },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o símbolo de campo obrigatório respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { label: 'bar', modelValue: 'foo', maxlength: 10, required: true },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o ícone respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key', modelValue: 'foo', maxlength: 10 },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza a mensagem de erro respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { erro: 'bar', modelValue: 'foo', maxlength: 10 },
            }).html()
        ).toMatchSnapshot();
    });

    test('atributos do componente e extras renderizados corretamente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    id: '123',
                    modelValue: 'foo',
                    maxlength: 10,
                    type: 'password',
                    extra: 'loren',
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('rendiza o componente de maneira diversa quando desabilitado respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    id: '123',
                    modelValue: 'foo',
                    maxlength: 10,
                    disabled: true,
                },
            }).html()
        ).toMatchSnapshot();
    });

    test('exibe o componente filho ContadorCaracteres quando o input recebe foco', async () => {
        document.body.innerHTML = `
        <div>
            <div id="app"></div>
        </div>
        `;

        const wrapper = mountFunction({
            props: { modelValue: 'foo', maxlength: 10 },
            attachTo: document.getElementById('app'),
        });

        const contadorCaracteres = wrapper.findComponent({ name: 'ContadorCaracteres' });
        const input = wrapper.get('input');

        expect(contadorCaracteres.isVisible()).toBe(false);
        await input.trigger('focus');
        expect(contadorCaracteres.isVisible()).toBe(true);
        await input.trigger('blur');
        expect(contadorCaracteres.isVisible()).toBe(false);
    });

    test('atualiza o v-model ao alterar o valor do elemento', () => {
        const parent = mount({
            data: () => {
                return { foo: 'bar' };
            },
            template: '<div> <TextInput v-model="foo" :maxlength="10"/> </div>',
            components: { TextInput: TextInput },
        });

        const input = parent.find('input');

        input.element.value = 'bar baz';
        input.trigger('input');

        expect(parent.vm.foo).toBe('bar baz');
    });

    test('dispara o evento onInput se o componente estiver habilitado utilizando máscara no valor emitido', () => {
        const wrapper = mountFunction({
            props: { erro: 'bar', modelValue: '', maxlength: 10, mascara: '##-##-####' },
        });

        const event = { target: { value: '30102000' } };

        wrapper.vm.onInput(event);

        expect(wrapper.emitted()['update:modelValue'][0]).toEqual(['30-10-2000']);
    });

    test('dispara o evento onInput se o componente estiver habilitado', () => {
        const wrapper = mountFunction({
            props: { erro: 'bar', modelValue: '', maxlength: 10 },
        });

        const event = { target: { value: 'foo' } };

        wrapper.vm.onInput(event);

        expect(wrapper.emitted()['update:modelValue'][0]).toEqual(['foo']);
    });

    test('não dispara o evento onInput se o componente estiver desabilitado', () => {
        const wrapper = mountFunction({
            props: { erro: 'bar', modelValue: '', maxlength: 10, disabled: true },
        });

        const event = { target: { value: 'foo' } };

        wrapper.vm.onInput(event);

        expect(wrapper.emitted()['update:modelValue']).toBeFalsy();
    });
});
