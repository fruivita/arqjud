/**
 * Testes para o componente Container.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Container from '@/Shared/Containers/Container.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Container, { ...options });
    };
});

describe('Container', () => {
    // Caminho feliz
    test('renderiza o componente respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });

    test('renderiza o componente com o slot respeitando o snapshot', () => {
        const wrapper = mountFunction({ slots: { default: 'foo' } });

        expect(wrapper.html()).toMatchSnapshot();
    });
});
