/**
 * Testes para o componente PorPagina.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import { perPageKey, updatePerPageKey } from '@/keys';
import PorPagina from '@/Shared/Tables/PorPagina.vue';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.stubGlobal('_dados', { paginacao: [10, 20, 30] });

vi.mock('@inertiajs/inertia-vue3', () => ({
    usePage: () => ({
        props: {
            value: { per_page: 20 },
        },
    }),
}));

let mountFunction;

beforeEach(() => {
    const pinia = () => createTestingPinia({ stubActions: false, createSpy: vi.fn });
    mountFunction = (options = {}) => {
        return mount(PorPagina, {
            global: {
                plugins: [pinia()],
                provide: {
                    [perPageKey]: 25,
                    [updatePerPageKey]: vi.fn,
                },
            },
            ...options,
        });
    };
});

describe('HeadingOrdenavel', () => {
    test('renderiza o componente respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });
});
