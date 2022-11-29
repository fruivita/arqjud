/**
 * Testes para o componente MensagemErro.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import MensagemErro from '@/Shared/Forms/MensagemErro.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(MensagemErro, { ...options });
    };
});

// Caminho feliz
describe('MensagemErro', () => {
    test('propriedades do componente estão definidas', () => {
        expect(MensagemErro.props).toMatchObject({
            erro: { type: String, required: true },
        });
    });

    test('renderiza o componente respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { erro: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });
});
