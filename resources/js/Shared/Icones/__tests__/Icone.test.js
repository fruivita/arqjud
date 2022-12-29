/**
 * Testes para o componente Icone.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Icone from '@/Shared/Icones/Icone.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

let mountFunction;

beforeEach(() => {
    mountFunction = (options = {}) => {
        return mount(Icone, { ...options });
    };
});

// Caminho feliz
describe('Icone', () => {
    test('propriedades do componente estão definidas', () => {
        expect(Icone.props).toMatchObject({
            nome: { type: String, required: true },
            class: { type: String, default: 'w-6 h-6' },
        });
    });

    const cases = [
        ['foo'],
        ['arrow-clockwise'],
        ['arrow-down-short'],
        ['arrow-up-short'],
        ['blockquote-left'],
        ['book'],
        ['bookshelf'],
        ['box2'],
        ['boxes'],
        ['box-arrow-in-right'],
        ['brightness-high'],
        ['building'],
        ['buildings'],
        ['calendar-event'],
        ['calendar-range'],
        ['caret-right'],
        ['cart'],
        ['check-circle'],
        ['clipboard'],
        ['dash'],
        ['dash-circle'],
        ['diagram-3'],
        ['door-closed'],
        ['door-open'],
        ['envelope'],
        ['eye'],
        ['files'],
        ['geo-alt'],
        ['git'],
        ['info-circle'],
        ['journal-bookmark'],
        ['journals'],
        ['joystick'],
        ['key'],
        ['layers'],
        ['link-45deg'],
        ['list'],
        ['list-nested'],
        ['moon-stars'],
        ['paperclip'],
        ['pencil-square'],
        ['person'],
        ['pin-map'],
        ['plus-circle'],
        ['printer'],
        ['question-circle'],
        ['quote'],
        ['safe'],
        ['save'],
        ['search'],
        ['signpost'],
        ['signpost-2'],
        ['symmetry-vertical'],
        ['tag'],
        ['trash'],
        ['three-dots-vertical'],
        ['x-circle'],
    ];

    test.each(cases)('renderiza o componente respeitando o snapshot', (icone) => {
        expect(mountFunction({ props: { nome: icone } }).html()).toMatchSnapshot();
    });
});
