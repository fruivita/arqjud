/**
 * Testes para o componente Alerta.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Alerta from '@/Shared/Containers/Alerta.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Alerta, { ...options });
    };
});

describe('Alerta', () => {
    // Caminho feliz
    test('renderiza o componente respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });

    test('renderiza o componente com o slot respeitando o snapshot', () => {
        const wrapper = mountFunction({ slots: { default: 'foo' } });

        expect(wrapper.html()).toMatchSnapshot();
    });
});
