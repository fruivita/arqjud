/**
 * Testes para o componente Row.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Row from '@/Shared/Tables/Row.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Row, { ...options });
    };
});

describe('Row', () => {
    // Caminho feliz
    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                slots: { default: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });
});
