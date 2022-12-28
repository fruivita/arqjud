<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Models\Usuario;
use App\Notifications\ProcessoDevolvido;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\Snapshots\assertMatchesHtmlSnapshot;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->dados = [
        'processo' => '11111111111111111111',
        'devolvido_em' => '2020-10-20 23:30:40',
        'url' => 'http://foo.bar',
    ];
});

// Caminho feliz
test('notificação ProcessoDevolvido envia a notificação para a queue', function () {
    $notificados = Usuario::factory(3)->create();

    Queue::fake();

    $notificacao = new ProcessoDevolvido(...$this->dados);

    Notification::send($notificados, $notificacao);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoDevolvido::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 3); // 1 por usuário
});

test('notificação ProcessoDevolvido envia a notificação pelos canais apropriados', function () {
    $notificados = Usuario::factory(3)->create();

    $notificacao = new ProcessoDevolvido(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificados, $notificacao);

    Notification::assertSentTo($notificados, ProcessoDevolvido::class, fn ($notification, $channels) => $channels === ['mail']);
});

test('notificação ProcessoDevolvido NÃO é enviada para usuários SEM email', function () {
    $notificado = Usuario::factory()->create(['email' => null]);

    $notificacao = new ProcessoDevolvido(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificado, $notificacao);

    Notification::assertNothingSent();
});

test('notificação ProcessoDevolvido envia email de acordo com o snapshot', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoDevolvido(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificado, $notificacao);

    Notification::assertSentTo($notificado, ProcessoDevolvido::class, function ($notification) use ($notificado) {
        assertMatchesHtmlSnapshot($notification->toMail($notificado)->render());

        return true;
    });
});

test('email da notificação ProcessoDevolvido possui título e markdown definidos', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoDevolvido(...$this->dados);

    $mailable = $notificacao->toMail($notificado);

    expect($mailable->subject)->toBe(__('Processo devolvido'))
        ->and($mailable->markdown)->toBe('emails.solicitacoes.devolvida')
        ->and($mailable->level)->toBe('info');
});

test('notificação ProcessoDevolvido possui canais, toArray e queues definidas', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoDevolvido(...$this->dados);

    expect($notificacao->via($notificado))->toMatchArray(['mail'])
        ->and($notificacao->toArray($notificado))->toBe($this->dados)
        ->and($notificacao->viaQueues())->toMatchArray(['mail' => EQueue::Baixa->value]);
});

test('notificação ProcessoDevolvido analisa corretamente se o email deve, por fim, ser enviado', function () {
    $usuario_com_email = Usuario::factory()->create();
    $usuario_sem_email = Usuario::factory()->create(['email' => null]);

    $notificacao = new ProcessoDevolvido(...$this->dados);

    expect($notificacao->shouldSend($usuario_sem_email, 'mail'))->toBeFalse()
        ->and($notificacao->shouldSend($usuario_com_email, 'mail'))->toBeTrue();
});
