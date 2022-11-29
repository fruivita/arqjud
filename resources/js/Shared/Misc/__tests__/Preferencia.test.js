/**
 * Testes para o componente Preferencia.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Preferencia from '@/Shared/Misc/Preferencia.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Preferencia, { ...options });
    };
});

// Caminho feliz
describe('Preferencia', () => {
    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                slots: { default: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('exibe o conteúdo escondido ao clicar no botão respeitando o snapshot', async () => {
        const wrapper = mountFunction({
            slots: { default: 'foo' },
        });

        await wrapper.find('[dusk="toggle"]').trigger('click');

        expect(wrapper.html()).toMatchSnapshot();
    });

    test('alterna a visibilidade do conteúdo do componente ao clicar no botão', async () => {
        const wrapper = mountFunction({
            slots: { default: 'foo' },
        });

        const preferencias = wrapper.get('[dusk="preferencias"]');

        expect(preferencias.isVisible()).toBeFalsy();

        await wrapper.get('[dusk="toggle"]').trigger('click');

        expect(preferencias.isVisible()).toBeTruthy();

        await wrapper.get('[dusk="toggle"]').trigger('click');

        expect(preferencias.isVisible()).toBeFalsy();
    });
});
