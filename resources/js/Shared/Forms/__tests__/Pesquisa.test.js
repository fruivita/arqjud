/**
 * Testes para o componente Pesquisa.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.stubGlobal('_dados', {});

vi.mock('@inertiajs/inertia-vue3', () => ({
    __esModule: true,
    ...vi.importActual('@inertiajs/inertia-vue3'),
    usePage: () => ({
        props: {
            value: {
                errors: {},
            },
        },
    }),
}));

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Pesquisa, { ...options });
    };
});

describe('Pesquisa', () => {
    test('propriedades estão definidas', () => {
        expect(Pesquisa.props).toMatchObject({
            modelValue: { type: String },
            maxlength: { type: [Number, String], default: 50 },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { modelValue: 'foo', maxlength: 30 },
            }).html()
        ).toMatchSnapshot();
    });

    test('atributos extras renderizados corretamente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { modelValue: 'foo', maxlength: 30, extra: 'loren' },
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
            props: { modelValue: 'foo', maxlength: 30 },
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
            template: '<div> <Pesquisa v-model="foo" /> </div>',
            components: { Pesquisa: Pesquisa },
        });

        const input = parent.find('input');

        input.element.value = 'bar baz';
        input.trigger('input');

        expect(parent.vm.foo).toBe('bar baz');
    });

    test('dispara o evento onInput', () => {
        const wrapper = mountFunction({
            props: { modelValue: '' },
        });

        const event = { target: { value: 'foo' } };

        wrapper.vm.onInput(event);

        expect(wrapper.emitted()['update:modelValue'][0]).toEqual(['foo']);
    });
});
