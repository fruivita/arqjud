/**
 * Testes para o componente Pagina.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Pagina from '@/Shared/Containers/Pagina.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Pagina, { ...options });
    };
});

describe('Pagina', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(Pagina.props).toMatchObject({
            titulo: { type: String },
        });
    });

    test('renderiza o componente sem o título respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });

    test('renderiza o componente com o título respeitando o snapshot', () => {
        const wrapper = mountFunction({ props: { titulo: 'foo' } });

        expect(wrapper.html()).toMatchSnapshot();
    });

    test('renderiza o componente com slot respeitando o snapshot', () => {
        const wrapper = mountFunction({ slots: { default: 'foo' } });

        expect(wrapper.html()).toMatchSnapshot();
    });
});
