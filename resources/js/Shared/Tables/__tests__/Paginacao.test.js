/**
 * Testes para o componente Paginacao.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Paginacao from '@/Shared/Tables/Paginacao.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Paginacao, { ...options });
    };
});

describe('Paginacao', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(Paginacao.props).toMatchObject({
            meta: { type: Object },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: {
                    meta: {
                        from: 1,
                        to: 3,
                        total: 3,
                        links: [
                            {
                                active: true,
                                label: 'loren',
                                url: null,
                            },
                            {
                                active: true,
                                label: 'foo',
                                url: 'foo.com',
                            },
                            {
                                active: false,
                                label: 'bar',
                                url: 'bar.com',
                            },
                            {
                                active: false,
                                label: 'baz',
                                url: 'baz.com',
                            },
                        ],
                    },
                },
            }).html()
        ).toMatchSnapshot();
    });
});
