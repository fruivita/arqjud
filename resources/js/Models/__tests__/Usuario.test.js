/**
 * Testes para o modelo Usuário.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import Usuario from '@/Models/Usuario';
import { describe, expect, test } from 'vitest';

describe('Usuario', () => {
    test('retorna o nome do usuário para exibição', () => {
        const usuario = new Usuario({
            matricula: '11111',
            nome: 'bar',
        });

        expect(usuario.nomeExibicao()).toBe('bar');
    });

    test('se não houver nome, usará a matrícula como nome de exibição', () => {
        const usuario = new Usuario({
            matricula: '11111',
            nome: '',
        });

        expect(usuario.nomeExibicao()).toBe('11111');
    });

    test('retorna os dados do último login completos', () => {
        const usuario = new Usuario({
            ultimo_login: 'foo',
            ip: 'bar',
        });

        expect(usuario.ultimoLogin()).toBe('foo (bar)');
    });

    test('retorna os dados do último login só com a data e hora', () => {
        const usuario = new Usuario({ ultimo_login: 'foo' });

        expect(usuario.ultimoLogin()).toBe('foo');
    });

    test('retorna os dados do último login só com ip', () => {
        const usuario = new Usuario({ ip: 'bar' });

        expect(usuario.ultimoLogin()).toBe('bar');
    });

    test('retorna vazio se não houver dados de login para exibir', () => {
        const usuario = new Usuario({});

        expect(usuario.ultimoLogin()).toBe('');
    });
});
