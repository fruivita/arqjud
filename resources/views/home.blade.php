{{--
    View padrão para usuários autenticados.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-layouts.app>

    <x-page :cabecalho="__('Home')">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Localidade::class)

                <x-link-card
                    icone="pin-map"
                    :href="route('arquivamento.cadastro.localidade.index')"
                    :texto="__('Gerenciar localidades')"
                    :title="__('Gerenciamento das localidades da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Predio::class)

                <x-link-card
                    icone="building"
                    :href="route('arquivamento.cadastro.predio.index')"
                    :texto="__('Gerenciar prédios')"
                    :title="__('Gerenciamento dos prédios da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Andar::class)

                <x-link-card
                    icone="layers"
                    :href="route('arquivamento.cadastro.andar.index')"
                    :texto="__('Gerenciar andares')"
                    :title="__('Gerenciamento dos andares da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Sala::class)

                <x-link-card
                    icone="door-closed"
                    :href="route('arquivamento.cadastro.sala.index')"
                    :texto="__('Gerenciar salas')"
                    :title="__('Gerenciamento das salas da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Estante::class)

                <x-link-card
                    icone="bookshelf"
                    :href="route('arquivamento.cadastro.estante.index')"
                    :texto="__('Estantes')"
                    :title="__('Gerenciamento das estantes da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Prateleira::class)

                <x-link-card
                    icone="list-nested"
                    :href="route('arquivamento.cadastro.prateleira.index')"
                    :texto="__('Prateleiras')"
                    :title="__('Gerenciamento das prateleiras da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Caixa::class)

                <x-link-card
                    icone="box2"
                    :href="route('arquivamento.cadastro.caixa.index')"
                    :texto="__('Gerenciar caixas')"
                    :title="__('Gerenciamento das caixas da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Configuracao::class)

                <x-link-card
                    icone="gear"
                    :href="route('administracao.configuracao.edit')"
                    :texto="__('Configurações da aplicação')"
                    :title="__('Gerenciamento das configurações de funcionamento da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Documentacao::class)

                <x-link-card
                    icone="book"
                    :href="route('administracao.documentacao.index')"
                    :texto="__('Documentação')"
                    :title="__('Gerenciamento da documentação das rotas da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ImportacaoCreate->value)

                <x-link-card
                    icone="usb-drive"
                    :href="route('administracao.importacao.create')"
                    :texto="__('Importação forçada de dados')"
                    :title="__('Execução de importação forçada de dados')"/>

            @endcan

            @can(\App\Enums\Policy::LogViewAny->value)

                <x-link-card
                    icone="file-earmark-text"
                    :href="route('administracao.log.index')"
                    :texto="__('Gerenciar logs')"
                    :title="__('Gerenciamento dos logs de funcionamento da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::DelegacaoViewAny->value)

                <x-link-card
                    icone="person-lines-fill"
                    :href="route('autorizacao.delegacao.index')"
                    :texto="__('Delegação de perfil')"
                    :title="__('Gerenciamento das delegações de perfis')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Perfil::class)

                <x-link-card
                    icone="award"
                    :href="route('autorizacao.perfil.index')"
                    :texto="__('Gerenciar perfis')"
                    :title="__('Gerenciamento dos perfis da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Permissao::class)

                <x-link-card
                    icone="vector-pen"
                    :href="route('autorizacao.permissao.index')"
                    :texto="__('Gerenciar permissões')"
                    :title="__('Gerenciamento das permissões da aplicação')"/>

            @endcan


            @can(\App\Enums\Policy::ViewAnyOrUpdate->value, \App\Models\Usuario::class)

                <x-link-card
                    icone="person-check"
                    :href="route('autorizacao.usuario.index')"
                    :texto="__('Gerenciar usuários')"
                    :title="__('Gerenciamento de usuários')"/>

            @endcan


            @can(\App\Enums\Policy::SimulacaoCreate->value)

                <x-link-card
                    icone="people"
                    :href="route('teste.simulacao.create')"
                    :texto="__('Simulação de uso')"
                    :title="__('Simulação de uso da aplicação')"/>

            @endcan

        </div>

    </x-page>

</x-layouts.app>
