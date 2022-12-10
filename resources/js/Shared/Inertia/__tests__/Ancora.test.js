/**
 * Testes para o componente Ancora.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Ancora from '@/Shared/Inertia/Ancora.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Ancora, { ...options });
    };
});

describe('Ancora', () => {
    test('propriedades estão definidas', () => {
        expect(Ancora.props).toMatchObject({
            href: { type: String, required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { href: 'http://foo.bar' },
            }).html()
        ).toMatchSnapshot();
    });
});
