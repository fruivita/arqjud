/**
 * Testes para o componente Card.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Card from '@/Shared/Misc/Card.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Card, { ...options });
    };
});

describe('Card', () => {
    // Caminho feliz
    const especies = ['info', 'alerta', 'perigo'];

    test('aceita apenas as espécies definidas', () => {
        const validator = Card.props.especie.validator;

        especies.forEach((especie) => expect(validator(especie)).toBe(true));
        expect(validator('foo')).toBe(false);
    });

    test('propriedades do componente estão definidas', () => {
        expect(Card.props).toMatchObject({
            titulo: { type: String, required: true },
            especie: { type: String, default: 'info' },
            texto: { type: [Number, String], required: true },
        });
    });

    test.each(especies)(
        'renderiza o componente das espécies específicas respeitando o snapshot',
        (especie) => {
            expect(
                mountFunction({
                    props: { titulo: 'foo', texto: 'bar', especie: especie },
                }).html()
            ).toMatchSnapshot();
        }
    );
});
