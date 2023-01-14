/**
 * Testes para os helpers dos objetos.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { nomeUsuario, ultimoLogin } from '@/Helpers/Usuario';
import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, test } from 'vitest';

setActivePinia(createPinia());

describe('Usuário', () => {
    test('retorna o nome do usuário para exibição', () => {
        const usuario = {
            matricula: '11111',
            nome: 'bar',
        };

        expect(nomeUsuario(usuario)).toBe('bar');
    });

    test('se não houver nome, usará a matrícula como nome de exibição', () => {
        const usuario = {
            matricula: '11111',
            nome: '',
        };

        expect(nomeUsuario(usuario)).toBe('11111');
    });

    test('retorna os dados do último login completos', () => {
        const usuario = {
            ultimo_login: 'foo',
            ip: 'bar',
        };

        expect(ultimoLogin(usuario)).toBe('foo (bar)');
    });

    test('retorna os dados do último login só com a data e hora', () => {
        const usuario = { ultimo_login: 'foo' };

        expect(ultimoLogin(usuario)).toBe('foo');
    });

    test('retorna os dados do último login só com ip', () => {
        const usuario = { ip: 'bar' };

        expect(ultimoLogin(usuario)).toBe('bar');
    });

    test('retorna vazio se não houver dados de login para exibir', () => {
        const usuario = {};

        expect(ultimoLogin(usuario)).toBe('');
    });
});
