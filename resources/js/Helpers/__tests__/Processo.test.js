/**
 * Testes para os helpers dos objetos.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { gp, localizacao } from '@/Helpers/Processo';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, test } from 'vitest';

setActivePinia(createPinia());

const __ = useTranslationsStore().__;

describe('Processo', () => {
    test('retorna a lotação do processo pronta para exibição', () => {
        const processo = {
            solicitacao_ativa: [
                {
                    entregue_em: '01/01/1900',
                    destino: {
                        sigla: 'foo',
                        nome: 'bar',
                    },
                },
            ],
        };

        expect(localizacao(processo)).toBe('foo - bar');
    });

    test('localizado no arquivo se não houver solicitação ativa', () => {
        const processo = {};

        expect(localizacao(processo)).toBe('No arquivo');
    });

    test('localizado no arquivo se não houver data de entrega', () => {
        const processo = {
            solicitacao_ativa: [
                {
                    destino: {
                        sigla: 'foo',
                        nome: 'bar',
                    },
                },
            ],
        };

        expect(localizacao(processo)).toBe('No arquivo');
    });

    test('retorna o texto se o processo é de guarda_permanente', () => {
        const processo = {
            guarda_permanente: true,
        };

        expect(gp(processo)).toBe(__('Sim'));
    });

    test('retorna o texto se o processo não é de guarda_permanente', () => {
        const processo = {
            guarda_permanente: false,
        };

        expect(gp(processo)).toBe(__('Não'));
    });
});
