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
            username: 'foo',
            nome: 'bar',
        });

        expect(usuario.nomeExibicao()).toBe('bar');
    });

    test('se não houver nome, usará a username como nome de exibição', () => {
        const usuario = new Usuario({
            username: 'foo',
            nome: '',
        });

        expect(usuario.nomeExibicao()).toBe('foo');
    });
});
