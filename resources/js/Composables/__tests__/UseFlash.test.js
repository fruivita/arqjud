/**
 * Testes para o composable UseFlash.
 *
 * @see https://vuejs.org/guide/scaling-up/testing.html
 * @see https://test-utils.vuejs.org/guide/
 * @see https://vitest.dev/
 */

import { flash } from '@/Composables/UseFlash';
import { createTestingPinia } from '@pinia/testing';
import { beforeEach, describe, expect, test, vi } from 'vitest';

let mensagem;
let inertiaMock;

inertiaMock = () => {
    vi.mock('@inertiajs/inertia-vue3', () => ({
        __esModule: true,
        ...vi.importActual('@inertiajs/inertia-vue3'),
        usePage: () => ({
            props: {
                value: {
                    flash: { ...mensagem },
                },
            },
        }),
    }));
};

vi.mock('vue-toastification', () => ({
    __esModule: true,
    ...vi.importActual('vue-toastification'),
    useToast: () => ({
        success: vi.fn(() => 'success'),
        info: vi.fn(() => 'info'),
        warning: vi.fn(() => 'warning'),
        error: vi.fn(() => 'error'),
    }),
}));

beforeEach(() => {
    createTestingPinia();
});

describe('useFlash', () => {
    // notificação e retorno esperado
    const cases = [
        [[{ sucesso: 'foo' }, 'success']],
        [[{ informacao: 'foo' }, 'info']],
        [[{ alerta: 'foo' }, 'warning']],
        [[{ erro: 'foo' }, 'error']],
    ];
    test.each(cases)(
        'mensagem flash apropriada é acionada de acordo com o tipo de mensagem',
        ([notificacao, retorno]) => {
            mensagem = notificacao;
            inertiaMock();
            expect(flash()).toBe(retorno);
        }
    );

    test('se a notificação for infomada, ela será exibida', () => {
        const notificacao = { sucesso: 'foo' };

        expect(flash(notificacao)).toBe('success');
    });

    test('se o tipo de mensagem não for permitida, lança falha no console', () => {
        mensagem = { Foo: 'foo' };
        inertiaMock();

        const erro = vi.spyOn(console, 'error').mockImplementation(() => {});
        const retorno = flash();

        expect(retorno).toBe(-1); // código de erro
        expect(erro).toHaveBeenCalledOnce();
    });
});
