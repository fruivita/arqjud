<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Models\Usuario;
use App\Notifications\ProcessoEntregue;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\Snapshots\assertMatchesHtmlSnapshot;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->dados = [
        'guia_numero' => '10/2000',
        'processos' => [
            [
                'numero' => '11111111111111111111',
                'qtd_volumes' => '10',
                'solicitante' => [
                    'matricula' => 'foo',
                    'nome' => 'foo bar',
                ],
            ],
            [
                'numero' => '22222222222222222222',
                'qtd_volumes' => '20',
                'solicitante' => [
                    'matricula' => 'loren',
                    'nome' => '',
                ],
            ],
        ],
        'recebedor' => 'ipson dolor',
        'destino' => 'lot foo',
        'entregue_em' => '2020-10-20 23:30:40',
        'por_guia' => false,
        'url' => 'http://foo.bar',
    ];
});

// Caminho feliz
test('notificação ProcessoEntregue envia a notificação para a queue', function () {
    $notificados = Usuario::factory(3)->create();

    Queue::fake();

    $notificacao = new ProcessoEntregue(...$this->dados);

    Notification::send($notificados, $notificacao);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoEntregue::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 3); // 1 por usuário
});

test('notificação ProcessoEntregue envia a notificação pelos canais apropriados', function () {
    $notificados = Usuario::factory(3)->create();

    $notificacao = new ProcessoEntregue(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificados, $notificacao);

    Notification::assertSentTo($notificados, ProcessoEntregue::class, fn ($notification, $channels) => $channels === ['mail']);
});

test('notificação ProcessoEntregue envia email BCC aos endereços informados', function () {
    $notificados = Usuario::factory(3)->create();

    $this->dados['email_terceiros'] = ['foo@bar.com', 'bar@taz.com'];

    $notificacao = new ProcessoEntregue(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificados, $notificacao);

    Notification::assertSentTo(
        $notificados,
        ProcessoEntregue::class,
        fn ($notification) => $notification->toMail(null)->bcc === [['foo@bar.com', null], ['bar@taz.com', null]]
    );
});

test('notificação ProcessoEntregue NÃO é enviada para usuários SEM email', function () {
    $notificado = Usuario::factory()->create(['email' => null]);

    $notificacao = new ProcessoEntregue(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificado, $notificacao);

    Notification::assertNothingSent();
});

test('notificação ProcessoEntregue envia email de acordo com o snapshot', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoEntregue(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificado, $notificacao);

    Notification::assertSentTo($notificado, ProcessoEntregue::class, function ($notification) use ($notificado) {
        assertMatchesHtmlSnapshot($notification->toMail($notificado)->render());

        return true;
    });
});

test('notificação ProcessoEntregue, quando efetivada por guia de remessa, envia email de acordo com o snapshot', function () {
    $notificado = Usuario::factory()->create();

    $this->dados['por_guia'] = true;

    $notificacao = new ProcessoEntregue(...$this->dados);

    Notification::fake();

    Notification::sendNow($notificado, $notificacao);

    Notification::assertSentTo($notificado, ProcessoEntregue::class, function ($notification) use ($notificado) {
        assertMatchesHtmlSnapshot($notification->toMail($notificado)->render());

        return true;
    });
});

test('email da notificação ProcessoEntregue possui título e markdown definidos', function () {
    $notificado = Usuario::factory()->create();

    $notificacao = new ProcessoEntregue(...$this->dados);

    $mailable = $notificacao->toMail($notificado);

    expect($mailable->subject)->toBe(__('Processos solicitados entregues'))
        ->and($mailable->markdown)->toBe('emails.solicitacoes.entregue')
        ->and($mailable->level)->toBe('info');
});

test('notificação ProcessoEntregue possui canais, toArray e queues definidas', function () {
    $notificado = Usuario::factory()->create();

    $this->dados['email_terceiros'] = ['foo@bar.com', 'bar@taz.com'];

    $notificacao = new ProcessoEntregue(...$this->dados);

    expect($notificacao->via($notificado))->toMatchArray(['mail'])
        ->and($notificacao->toArray($notificado))->toBe($this->dados)
        ->and($notificacao->viaQueues())->toMatchArray(['mail' => EQueue::Baixa->value]);
});

test('notificação ProcessoEntregue analisa corretamente se o email deve, por fim, ser enviado', function () {
    $usuario_com_email = Usuario::factory()->create();
    $usuario_sem_email = Usuario::factory()->create(['email' => null]);

    $notificacao = new ProcessoEntregue(...$this->dados);

    expect($notificacao->shouldSend($usuario_sem_email, 'mail'))->toBeFalse()
        ->and($notificacao->shouldSend($usuario_com_email, 'mail'))->toBeTrue();
});
