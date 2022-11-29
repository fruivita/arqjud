/**
 * Testes para Pinia Store StatusRequisicaoStore.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 * @see https://pinia.vuejs.org/cookbook/testing.html#unit-testing-a-store
 */

import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, test } from 'vitest';

beforeEach(() => {
    setActivePinia(createPinia());
});

// Caminho feliz
describe('StatusRequisicaoStore', () => {
    test('status inicial da requisição é falso, mas pode ser atualizado posteriormente', () => {
        const statusRequisicao = useStatusRequisicaoStore();

        expect(statusRequisicao.processando).toBe(false);
        statusRequisicao.setStatus(true);
        expect(statusRequisicao.processando).toBe(true);
        statusRequisicao.setStatus(false);
        expect(statusRequisicao.processando).toBe(false);
    });
});
