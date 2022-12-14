<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Models\Usuario;
use App\Notifications\ProcessoSolicitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\Snapshots\assertMatchesHtmlSnapshot;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->dados = [
        'processos' => ['11111111111111111111', '22222222222222222222', '33333333333333333333'],
        'solicitante' => 'foo',
        'lotacao_destinataria' => 'bar',
        'solicitada_em' => '2020-10-20 23:30:40',
        'url' => 'http://foo.bar',
    ];
});

// Caminho feliz
test('notificação ProcessoSolicitado envia a notificação para a queue', function () {
    $notificados = Usuario::factory(3)->create();

    Queue::fake();

    $notificacao = new ProcessoSolicitado(...$this->dados);

    Notification::send($notificados, $notificacao);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoSolicitado::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 3); // 1 por usuário
});

test('notificação ProcessoSolicitado envia a notificação pelos canais apropriados', function () {
    $notificados = Usuario::factory(3)->create();

    $notificacao = new ProcessoSolicitado(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificados, $notificacao);

    Notification::assertSentTo($notificados, ProcessoSolicitado::class, fn ($notification, $channels) => $channels === ['mail']);
});

test('notificação ProcessoSolicitado NÃO é enviada para usuários SEM email', function () {
    $notificado = Usuario::factory()->create(['email' => null]);

    $notificacao = new ProcessoSolicitado(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificado, $notificacao);

    Notification::assertNothingSent();
});

test('notificação ProcessoSolicitado envia email de acordo com o snapshot', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoSolicitado(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificado, $notificacao);

    Notification::assertSentTo($notificado, ProcessoSolicitado::class, function ($notification) use ($notificado) {
        assertMatchesHtmlSnapshot($notification->toMail($notificado)->render());

        return true;
    });
});

test('email da notificação ProcessoSolicitado possui título e markdown definidos', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoSolicitado(...$this->dados);

    $mailable = $notificacao->toMail($notificado);

    expect($mailable->subject)->toBe(__('Solicitação de processos criada'))
        ->and($mailable->markdown)->toBe('emails.solicitacoes.solicitada')
        ->and($mailable->level)->toBe('info');
});

test('notificação ProcessoSolicitado possui canais, toArray e queues definidas', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoSolicitado(...$this->dados);

    expect($notificacao->via($notificado))->toBe(['mail'])
        ->and($notificacao->toArray($notificado))->toBe($this->dados)
        ->and($notificacao->viaQueues())->toBe(['mail' => EQueue::Baixa->value]);
});

test('notificação ProcessoSolicitado analisa corretamente se o email deve, por fim, ser enviado', function () {
    $usuario_com_email = Usuario::factory()->create();
    $usuario_sem_email = Usuario::factory()->create(['email' => null]);

    $notificacao = new ProcessoSolicitado(...$this->dados);

    expect($notificacao->shouldSend($usuario_sem_email, 'mail'))->toBeFalse()
        ->and($notificacao->shouldSend($usuario_com_email, 'mail'))->toBeTrue();
});
