/**
 * Testes para Pinia Store TranslationsStore.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 * @see https://pinia.vuejs.org/cookbook/testing.html#unit-testing-a-store
 */

import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.stubGlobal('_translations', {
    foo: 'Fooo',
    bar: 'Barr',
    'foo :bar :baz': 'foo :bar :baz',
});

let __;

beforeEach(() => {
    setActivePinia(createPinia());
    __ = useTranslationsStore().__;
});

describe('TranslationsStore', () => {
    test('retorna a tradução existente', () => {
        expect(__('foo')).toBe('Fooo');
    });

    test('retorna a própria chave a tradução for inexistente', () => {
        expect(__('baz')).toBe('baz');
    });

    test('substitui os trechos informados na tradução', () => {
        expect(__('foo :bar :baz', { bar: 'loren', baz: 'ipsun' })).toBe('foo loren ipsun');
    });
});
