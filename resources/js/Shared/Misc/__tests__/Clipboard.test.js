/**
 * Testes para o componente Clipboard.
 *
 * @link https://vuejs.org/guide/scaling-up/testing.html
 * @link https://test-utils.vuejs.org/guide/
 * @link https://vitest.dev/
 */

import Clipboard from '@/Shared/Misc/Clipboard.vue';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';

let mountFunction;

let clipboardContents = '';

beforeEach(() => {
    createTestingPinia();

    Object.assign(navigator, {
        clipboard: {
            writeText: vi.fn((text) => {
                clipboardContents = text;
            }),
            readText: vi.fn(() => clipboardContents),
        },
    });

    mountFunction = (options = {}) => {
        return mount(Clipboard, { ...options });
    };
});

describe('Clipboard', () => {
    test('propriedades do componente estão definidas', () => {
        expect(Clipboard.props).toMatchObject({
            copiavel: { type: String },
        });
    });

    test('renderiza o componente default respeitando o snapshot', () => {
        expect(
            mountFunction({
                props: { copiavel: 'foo' },
            }).html()
        ).toMatchSnapshot();
    });

    test('copia o conteúdo para o Clipboard ao clicar no botão', async () => {
        const wrapper = mountFunction({
            props: { copiavel: 'foo' },
        });

        expect(clipboardContents).toBe('');

        await wrapper.get('button').trigger('click');

        expect(clipboardContents).toBe('foo');

        clipboardContents = '';
    });
});
