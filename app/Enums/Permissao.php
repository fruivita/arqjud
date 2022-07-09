<?php

namespace App\Enums;

/*
 * Ids das permissões registradas no banco de dados.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/authorization
 */
enum Permissao: int
{
    // Configuração
    case ConfiguracaoView = 100002;
    case ConfiguracaoUpdate = 100004;
    // Delegação
    case DelegacaoViewAny = 110001;
    case DelegacaoCreate = 110003;
    // Documentação
    case DocumentacaoViewAny = 120001;
    case DocumentacaoView = 120002;
    case DocumentacaoCreate = 120003;
    case DocumentacaoUpdate = 120004;
    case DocumentacaoDelete = 120005;
    // Importação
    case ImportacaoCreate = 130003;
    // Log
    case LogViewAny = 140001;
    case LogDelete = 140005;
    case LogDownload = 140101;
    // Permissão
    case PermissaoViewAny = 150001;
    case PermissaoView = 150002;
    case PermissaoUpdate = 150004;
    // Perfil
    case PerfilViewAny = 160001;
    case PerfilView = 160002;
    case PerfilUpdate = 160004;
    // Simulação
    case SimulacaoCreate = 170003;
    // Usuário
    case UsuarioViewAny = 180001;
    case UsuarioUpdate = 180004;

    // Localidade
    case LocalidadeViewAny = 600001;
    case LocalidadeView = 600002;
    case LocalidadeCreate = 600003;
    case LocalidadeUpdate = 600004;
    case LocalidadeDelete = 600005;
    // Prédio
    case PredioViewAny = 610001;
    case PredioView = 610002;
    case PredioCreate = 610003;
    case PredioUpdate = 610004;
    case PredioDelete = 610005;
    // Andar
    case AndarViewAny = 620001;
    case AndarView = 620002;
    case AndarCreate = 620003;
    case AndarUpdate = 620004;
    case AndarDelete = 620005;
    // Sala
    case SalaViewAny = 630001;
    case SalaView = 630002;
    case SalaCreate = 630003;
    case SalaUpdate = 630004;
    case SalaDelete = 630005;
    // Estante
    case EstanteViewAny = 640001;
    case EstanteView = 640002;
    case EstanteCreate = 640003;
    case EstanteUpdate = 640004;
    case EstanteDelete = 640005;
    // Prateleira
    case PrateleiraViewAny = 650001;
    case PrateleiraView = 650002;
    case PrateleiraCreate = 650003;
    case PrateleiraUpdate = 650004;
    case PrateleiraDelete = 650005;
    // Caixa
    case CaixaViewAny = 660001;
    case CaixaView = 660002;
    case CaixaCreate = 660003;
    case CaixaUpdate = 660004;
    case CaixaDelete = 660005;
    case CaixaCreateMany = 660101;
    // Volume da caixa
    case VolumeCaixaViewAny = 670001;
    case VolumeCaixaView = 670002;
    case VolumeCaixaCreate = 670003;
    case VolumeCaixaUpdate = 670004;
    case VolumeCaixaDelete = 670005;
    // Processo
    case ProcessoViewAny = 680001;
    case ProcessoView = 680002;
    case ProcessoCreate = 680003;
    case ProcessoUpdate = 680004;
}
