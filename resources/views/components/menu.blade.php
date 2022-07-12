{{--
    Menu principal.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-menu.grupo :nome="__('Funcionalidades')">

    <x-menu.alternador-tema/>


    @auth

        <x-menu.fake-link
            icone="person"
            :texto="auth()->user()->paraHumano()"/>


        <x-menu.logout/>

    @else

        <x-menu.link
            class="{{ request()->routeIs('login') ? 'ativo': '' }}"
            icone="person"
            :href="route('login')"
            :texto="__('Login')"
            :title="__('Ir para a página de login')"/>

    @endauth

</x-menu.grupo>


@auth

    @if (
        auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Caixa::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Localidade::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Predio::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Andar::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Sala::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Estante::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Prateleira::class)
    )

        <x-menu.grupo :nome="__('Cadastro')">

            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Localidade::class)

                <x-menu.link
                    class="{{ request()->routeIs('arquivamento.cadastro.localidade.*') ? 'ativo': '' }}"
                    icone="pin-map"
                    :href="route('arquivamento.cadastro.localidade.index')"
                    :texto="__('Localidades')"
                    :title="__('Gerenciamento de localidades')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Predio::class)

                <x-menu.link
                    class="{{ request()->routeIs('arquivamento.cadastro.predio.*') ? 'ativo': '' }}"
                    icone="building"
                    :href="route('arquivamento.cadastro.predio.index')"
                    :texto="__('Prédios')"
                    :title="__('Gerenciamento de prédios')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Andar::class)

                <x-menu.link
                    class="{{ request()->routeIs('arquivamento.cadastro.andar.*') ? 'ativo': '' }}"
                    icone="layers"
                    :href="route('arquivamento.cadastro.andar.index')"
                    :texto="__('Andares')"
                    :title="__('Gerenciamento de andares')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Sala::class)

                <x-menu.link
                    class="{{ request()->routeIs('arquivamento.cadastro.sala.*') ? 'ativo': '' }}"
                    icone="door-closed"
                    :href="route('arquivamento.cadastro.sala.index')"
                    :texto="__('Salas')"
                    :title="__('Gerenciamento de salas')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Estante::class)

                <x-menu.link
                    class="{{ request()->routeIs('arquivamento.cadastro.estante.*') ? 'ativo': '' }}"
                    icone="bookshelf"
                    :href="route('arquivamento.cadastro.estante.index')"
                    :texto="__('Estantes')"
                    :title="__('Gerenciamento de estantes')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Prateleira::class)

                <x-menu.link
                    class="{{ request()->routeIs('arquivamento.cadastro.prateleira.*') ? 'ativo': '' }}"
                    icone="list-nested"
                    :href="route('arquivamento.cadastro.prateleira.index')"
                    :texto="__('Prateleiras')"
                    :title="__('Gerenciamento de prateleiras')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Caixa::class)

                <x-menu.link
                    class="{{ request()->routeIs('arquivamento.cadastro.caixa.*') ? 'ativo': '' }}"
                    icone="box2"
                    :href="route('arquivamento.cadastro.caixa.index')"
                    :texto="__('Caixas')"
                    :title="__('Gerenciamento de caixas')"/>

            @endcan

        </x-menu.grupo>

    @endif


    @if (
            auth()->user()->can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Configuracao::class)
            || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Documentacao::class)
            || auth()->user()->can(\App\Enums\Policy::ImportacaoCreate->value)
            || auth()->user()->can(\App\Enums\Policy::LogViewAny->value)
        )

        <x-menu.grupo :nome="__('Administração')">

            @can (\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Configuracao::class)

                <x-menu.link
                    class="{{ request()->routeIs('administracao.configuracao.*') ? 'ativo': '' }}"
                    icone="gear"
                    :href="route('administracao.configuracao.edit')"
                    :texto="__('Configuração')"
                    :title="__('Gerenciamento das configurações de funcionamento da aplicação')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Documentacao::class)

                <x-menu.link
                    class="{{ request()->routeIs('administracao.documentacao.*') ? 'ativo': '' }}"
                    icone="book"
                    :href="route('administracao.documentacao.index')"
                    :texto="__('Documentação')"
                    :title="__('Gerenciamento da documentação das rotas da aplicação')"/>

            @endcan


            @can (\App\Enums\Policy::ImportacaoCreate->value)

                <x-menu.link
                    class="{{ request()->routeIs('administracao.importacao.*') ? 'ativo': '' }}"
                    icone="usb-drive"
                    :href="route('administracao.importacao.create')"
                    :texto="__('Importação')"
                    :title="__('Execução de importação forçada de dados')"/>

            @endcan


            @can (\App\Enums\Policy::LogViewAny->value)

                <x-menu.link
                    class="{{ request()->routeIs('administracao.log.*') ? 'ativo': '' }}"
                    icone="file-earmark-text"
                    :href="route('administracao.log.index')"
                    :texto="__('Logs')"
                    :title="__('Gerenciamento dos logs de funcionamento da aplicação')"/>

            @endcan

        </x-menu.grupo>

    @endif


    @if (
        auth()->user()->can(\App\Enums\Policy::DelegacaoViewAny->value)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Perfil::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Permissao::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAnyOrUpdate->value, \App\Models\Usuario::class)
    )

        <x-menu.grupo :nome="__('Autorizações')">

            @can (\App\Enums\Policy::DelegacaoViewAny->value)

                <x-menu.link
                    class="{{ request()->routeIs('autorizacao.delegacao.*') ? 'ativo': '' }}"
                    icone="person-lines-fill"
                    :href="route('autorizacao.delegacao.index')"
                    :texto="__('Delegação')"
                    :title="__('Gerenciamento das delegações de perfis')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Perfil::class)

                <x-menu.link
                    class="{{ request()->routeIs('autorizacao.perfil.*') ? 'ativo': '' }}"
                    icone="award"
                    :href="route('autorizacao.perfil.index')"
                    :texto="__('Perfis')"
                    :title="__('Gerenciamento dos perfis da aplicação')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAny->value, \App\Models\Permissao::class)

                <x-menu.link
                    class="{{ request()->routeIs('autorizacao.permissao.*') ? 'ativo': '' }}"
                    icone="vector-pen"
                    :href="route('autorizacao.permissao.index')"
                    :texto="__('Permissões')"
                    :title="__('Gerenciamento das permissões da aplicação')"/>

            @endcan


            @can (\App\Enums\Policy::ViewAnyOrUpdate->value, \App\Models\Usuario::class)

                <x-menu.link
                    class="{{ request()->routeIs('autorizacao.usuario.*') ? 'ativo': '' }}"
                    icone="person-check"
                    :href="route('autorizacao.usuario.index')"
                    :texto="__('Usuários')"
                    :title="__('Gerenciamento de usuários')"/>

            @endcan

        </x-menu.grupo>

    @endif


    @if (
        auth()->user()->can(\App\Enums\Policy::SimulacaoCreate->value)
    )

        <x-menu.grupo :nome="__('Testes')">

            @can (\App\Enums\Policy::SimulacaoCreate->value)

                <x-menu.link
                    class="{{ request()->routeIs('teste.simulacao.*') ? 'ativo': '' }}"
                    icone="people"
                    :href="route('teste.simulacao.create')"
                    :texto="__('Simulação')"
                    :title="__('Simulação de uso da aplicação')"/>

            @endcan

        </x-menu.grupo>

    @endif

@endauth
