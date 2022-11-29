/**
 * Testes para o componente TheFooter.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 * @link https://stackoverflow.com/questions/69919632/how-to-test-vue3-and-intertia-with-jest
 */

import TheFooter from '@/Shared/Misc/TheFooter.vue';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.stubGlobal('_dados', {
    app_nome: 'Foo',
    app_nome_completo: 'Foo Bar',
    app_versao: '1.0.0',
});

let mountFunction;

beforeEach(() => {
    const pinia = () => createTestingPinia({ stubActions: false });

    mountFunction = (options = {}) => {
        return mount(TheFooter, {
            global: {
                plugins: [pinia()],
            },
            ...options,
        });
    };
});

describe('TheFooter', () => {
    test('renderiza o componente respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });
});
