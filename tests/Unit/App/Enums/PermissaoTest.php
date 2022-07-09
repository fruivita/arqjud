<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;

// Caminho feliz
test('id das permissões para administração das configurações estão definidas', function () {
    expect(Permissao::ConfiguracaoView->value)->toBe(100002)
    ->and(Permissao::ConfiguracaoUpdate->value)->toBe(100004);
});

test('id das permissões para administração das delegações estão definidas', function () {
    expect(Permissao::DelegacaoViewAny->value)->toBe(110001)
    ->and(Permissao::DelegacaoCreate->value)->toBe(110003);
});

test('id das permissões para administração dadocumentação da aplicação estão definidas', function () {
    expect(Permissao::DocumentacaoViewAny->value)->toBe(120001)
    ->and(Permissao::DocumentacaoView->value)->toBe(120002)
    ->and(Permissao::DocumentacaoCreate->value)->toBe(120003)
    ->and(Permissao::DocumentacaoUpdate->value)->toBe(120004)
    ->and(Permissao::DocumentacaoDelete->value)->toBe(120005);
});

test('id das permissões para administração da importação de dados estão definidas', function () {
    expect(Permissao::ImportacaoCreate->value)->toBe(130003);
});

test('id das permissões para administração dos logs da aplicação estão definidas', function () {
    expect(Permissao::LogViewAny->value)->toBe(140001)
    ->and(Permissao::LogDelete->value)->toBe(140005)
    ->and(Permissao::LogDownload->value)->toBe(140101);
});

test('id das permissões para administração das permissões estão definidas', function () {
    expect(Permissao::PermissaoViewAny->value)->toBe(150001)
    ->and(Permissao::PermissaoView->value)->toBe(150002)
    ->and(Permissao::PermissaoUpdate->value)->toBe(150004);
});

test('id das permissões para administração dos perfis estão definidas', function () {
    expect(Permissao::PerfilViewAny->value)->toBe(160001)
    ->and(Permissao::PerfilView->value)->toBe(160002)
    ->and(Permissao::PerfilUpdate->value)->toBe(160004);
});

test('id das permissões para administração das simulações de uso estão definidas', function () {
    expect(Permissao::SimulacaoCreate->value)->toBe(170003);
});

test('id das permissões para administração dos usuários estão definidas', function () {
    expect(Permissao::UsuarioViewAny->value)->toBe(180001)
    ->and(Permissao::UsuarioUpdate->value)->toBe(180004);
});

test('id das permissões para administração das localidades estão definidas', function () {
    expect(Permissao::LocalidadeViewAny->value)->toBe(600001)
    ->and(Permissao::LocalidadeView->value)->toBe(600002)
    ->and(Permissao::LocalidadeCreate->value)->toBe(600003)
    ->and(Permissao::LocalidadeUpdate->value)->toBe(600004)
    ->and(Permissao::LocalidadeDelete->value)->toBe(600005);
});

test('id das permissões para administração dos prédios estão definidas', function () {
    expect(Permissao::PredioViewAny->value)->toBe(610001)
    ->and(Permissao::PredioView->value)->toBe(610002)
    ->and(Permissao::PredioCreate->value)->toBe(610003)
    ->and(Permissao::PredioUpdate->value)->toBe(610004)
    ->and(Permissao::PredioDelete->value)->toBe(610005);
});

test('id das permissões para administração das andares estão definidas', function () {
    expect(Permissao::AndarViewAny->value)->toBe(620001)
    ->and(Permissao::AndarView->value)->toBe(620002)
    ->and(Permissao::AndarCreate->value)->toBe(620003)
    ->and(Permissao::AndarUpdate->value)->toBe(620004)
    ->and(Permissao::AndarDelete->value)->toBe(620005);
});

test('id das permissões para administração dos salas estão definidas', function () {
    expect(Permissao::SalaViewAny->value)->toBe(630001)
    ->and(Permissao::SalaView->value)->toBe(630002)
    ->and(Permissao::SalaCreate->value)->toBe(630003)
    ->and(Permissao::SalaUpdate->value)->toBe(630004)
    ->and(Permissao::SalaDelete->value)->toBe(630005);
});

test('id das permissões para administração das estantes estão definidas', function () {
    expect(Permissao::EstanteViewAny->value)->toBe(640001)
    ->and(Permissao::EstanteView->value)->toBe(640002)
    ->and(Permissao::EstanteCreate->value)->toBe(640003)
    ->and(Permissao::EstanteUpdate->value)->toBe(640004)
    ->and(Permissao::EstanteDelete->value)->toBe(640005);
});

test('id das permissões para administração das prateleiras estão definidas', function () {
    expect(Permissao::PrateleiraViewAny->value)->toBe(650001)
    ->and(Permissao::PrateleiraView->value)->toBe(650002)
    ->and(Permissao::PrateleiraCreate->value)->toBe(650003)
    ->and(Permissao::PrateleiraUpdate->value)->toBe(650004)
    ->and(Permissao::PrateleiraDelete->value)->toBe(650005);
});

test('id das permissões para administração das caixas estão definidas', function () {
    expect(Permissao::CaixaViewAny->value)->toBe(660001)
    ->and(Permissao::CaixaView->value)->toBe(660002)
    ->and(Permissao::CaixaCreate->value)->toBe(660003)
    ->and(Permissao::CaixaUpdate->value)->toBe(660004)
    ->and(Permissao::CaixaDelete->value)->toBe(660005)
    ->and(Permissao::CaixaCreateMany->value)->toBe(660101);
});

test('id das permissões para administração dos volumes das caixas estão definidas', function () {
    expect(Permissao::VolumeCaixaViewAny->value)->toBe(670001)
    ->and(Permissao::VolumeCaixaView->value)->toBe(670002)
    ->and(Permissao::VolumeCaixaCreate->value)->toBe(670003)
    ->and(Permissao::VolumeCaixaUpdate->value)->toBe(670004)
    ->and(Permissao::VolumeCaixaDelete->value)->toBe(670005);
});

test('id das permissões para administração dos processos estão definidas', function () {
    expect(Permissao::ProcessoViewAny->value)->toBe(680001)
    ->and(Permissao::ProcessoView->value)->toBe(680002)
    ->and(Permissao::ProcessoCreate->value)->toBe(680003)
    ->and(Permissao::ProcessoUpdate->value)->toBe(680004);
});
