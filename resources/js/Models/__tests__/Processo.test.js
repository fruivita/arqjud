/**
 * Testes para o modelo Processo.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import Processo from '@/Models/Processo';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, test } from 'vitest';

setActivePinia(createPinia());

describe('Processo', () => {
    const __ = useTranslationsStore().__;

    test('retorna a lotação do processo pronta para exibição', () => {
        const processo = new Processo({
            solicitacao_ativa: {
                entregue_em: '01/01/1900',
                destino: {
                    sigla: 'foo',
                    nome: 'bar',
                },
            },
        });

        expect(processo.localizacao()).toBe('foo - bar');
    });

    test('localizado no arquivo se não houver solicitação ativa', () => {
        const processo = new Processo();

        expect(processo.localizacao()).toBe('No arquivo');
    });

    test('localizado no arquivo se não houver data de entrega', () => {
        const processo = new Processo({
            solicitacao_ativa: {
                destino: {
                    sigla: 'foo',
                    nome: 'bar',
                },
            },
        });

        expect(processo.localizacao()).toBe('No arquivo');
    });

    test('retorna o texto se o processo é de guarda_permanente', () => {
        const processo = new Processo({
            guarda_permanente: true,
        });

        expect(processo.gp()).toBe(__('Sim'));
    });

    test('retorna o texto se o processo não é de guarda_permanente', () => {
        const processo = new Processo({
            guarda_permanente: false,
        });

        expect(processo.gp()).toBe(__('Não'));
    });
});
