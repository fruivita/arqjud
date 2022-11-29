/**
 * Testes para o componente ThemeToggler.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import ThemeToggler from '@/Shared/Togglers/ThemeToggler.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(ThemeToggler, { ...options });
    };
});

describe('ThemeToggler', () => {
    // Caminho feliz
    test('renderiza o componente respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });

    test('renderiza o ícone correto de acordo com o tema escolhido respeitando o snapshot', async () => {
        const wrapper = mountFunction();

        expect(mountFunction().html()).toMatchSnapshot();

        await wrapper.get('button').trigger('click');

        expect(mountFunction().html()).toMatchSnapshot();
    });
});
