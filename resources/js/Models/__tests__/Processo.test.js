/**
 * Testes para o modelo Processo.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import Processo from '@/Models/Processo';
import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, test } from 'vitest';

setActivePinia(createPinia());

describe('Processo', () => {
    test('retorna a lotação do processo pronta para exibição', () => {
        const processo = new Processo({
            solicitacao_ativa: {
                entregue_em: '01/01/1900',
                lotacao_destinataria: {
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
                lotacao_destinataria: {
                    sigla: 'foo',
                    nome: 'bar',
                },
            },
        });

        expect(processo.localizacao()).toBe('No arquivo');
    });
});
