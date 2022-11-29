/**
 * Testes para o componente ButtonFlutuante.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import ButtonFlutuante from '@/Shared/Buttons/ButtonFlutuante.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(ButtonFlutuante, { ...options });
    };
});

describe('ButtonFlutuante', () => {
    // Caminho feliz
    test('propriedades do componente estão definidas', () => {
        expect(ButtonFlutuante.props).toMatchObject({
            icone: { type: String, required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { icone: 'key' },
            }).html()
        ).toMatchSnapshot();
    });
});
