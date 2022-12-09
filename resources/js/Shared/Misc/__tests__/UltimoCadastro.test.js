/**
 * Testes para o componente UltimoCadastro.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import UltimoCadastro from '@/Shared/Misc/UltimoCadastro.vue';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    setActivePinia(createPinia());

    mountFunction = (options = {}) => {
        return mount(UltimoCadastro, { ...options });
    };
});

describe('UltimoCadastro', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(UltimoCadastro.props).toMatchObject({
            href: { type: String },
            texto: { type: String },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { href: 'http://foo.bar', texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('renderiza o componente sem as suas propriedades respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });
});
