/**
 * Testes para o componente Heading.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Heading from '@/Shared/Tables/Heading.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Heading, { ...options });
    };
});

describe('Heading', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(Heading.props).toMatchObject({
            texto: { type: String, default: '' },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { texto: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('não renderiza o texto do componente respeitando o snapshot', () => {
        expect(mountFunction({ slots: { default: 'foo' } }).html()).toMatchSnapshot();
    });
});
