/**
 * Testes para o componente TheMenu.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 * @link https://stackoverflow.com/questions/69919632/how-to-test-vue3-and-intertia-with-jest
 */

import TheMenu from '@/Shared/Menus/TheMenu.vue';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.stubGlobal('_dados', {
    app_nome: 'Foo',
    orgao_sigla: 'BAZ',
});

vi.mock('@inertiajs/inertia-vue3', () => ({
    __esModule: true,
    ...vi.importActual('@inertiajs/inertia-vue3'),
    usePage: () => ({
        props: {
            value: {
                auth: {
                    menu: [
                        {
                            nome: 'foo',
                            links: [
                                {
                                    icone: 'key',
                                    href: 'http://exemplo-1.com',
                                    texto: 'texto-1',
                                    ativo: true,
                                },
                            ],
                        },
                        {
                            nome: 'bar',
                            links: [
                                {
                                    icone: 'person',
                                    href: 'http://exemplo-2.com',
                                    texto: 'texto-2',
                                    ativo: false,
                                },
                                {
                                    icone: 'person',
                                    href: 'http://exemplo-3.com',
                                    texto: 'texto-3',
                                    ativo: false,
                                },
                            ],
                        },
                    ],
                    home: 'http://home.foo',
                    logout: 'http://logout.foo',
                },
            },
        },
    }),
}));

let mountFunction;

beforeEach(() => {
    createTestingPinia({ stubActions: false });

    mountFunction = (options = {}) => {
        return mount(TheMenu, { ...options });
    };
});

describe('TheMenu', () => {
    test('renderiza o componente respeitando o snapshot', () => {
        expect(mountFunction().html()).toMatchSnapshot();
    });
});
