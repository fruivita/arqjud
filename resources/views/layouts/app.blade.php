{{--
    Default Master Page.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
    @see https://dev.to/timosville/sticky-footer-using-tailwind-css-225p
--}}


<!DOCTYPE html>
<html
    x-data="{ modoEscuro : false }"
    x-bind:class="modoEscuro ? 'dark' : ''"
    x-init="
        modoEscuro = JSON.parse(localStorage.getItem('modoEscuro'));
        $watch('modoEscuro', value => localStorage.setItem('modoEscuro', JSON.stringify(value)));"
    lang="{{ str_replace('_', '-', App::currentLocale()) }}"
>

    <head>

        <meta charset="UTF-8">


        <meta name="viewport" content="width=device-width, initial-scale=1.0">


        {{-- Ccs/tailwind/livewire --}}
        <link href="{{ mix('/css/teal.css') }}" rel="stylesheet">
        @livewireStyles


        {{-- javascript --}}
        <script src="{{ mix('/js/manifest.js') }}"></script>
        <script src="{{ mix('/js/vendor.js') }}"></script>


        <title>{{ config('app.name') }}</title>

    </head>


    <body x-cloak class="bg-primaria-50 duration-500 text-primaria-900 text-xl transition dark:bg-secundaria-900 dark:text-secundaria-50">

        <div x-data="{ menuVisivel : false }">

            {{-- exibe / esconde o menu de navegação --}}
            <x-menu.alternador class="z-20"/>


            {{-- navegação / menu side menu --}}
            <nav x-bind:class="menuVisivel ? '' : 'hidden'" class="bg-primaria-200 border-r-4 border-primaria-900 fixed inset-0 overflow-y-auto pt-16 px-3 w-72 z-10 dark:bg-secundaria-700 dark:border-secundaria-50 lg:block">

                {{-- Logo / Home --}}
                <header class="flex items-center justify-center">

                    <a

                        @auth

                            href="{{ route('home') }}" title="{{ __('Ir para a página inicial') }}"

                        @else

                            href="{{ route('login') }}" title="{{ __('Ir para a página de login') }}"

                        @endauth

                        class="bg-primaria-500 flex font-extrabold items-center h-24 justify-center outline-none rounded-full text-primaria-50 transition w-24 hover:bg-primaria-700 focus:ring focus:ring-primaria-300"
                    >

                        {{ config('app.name') }}

                    </a>

                </header>


                {{-- menu (links) propriamente dito --}}
                <x-menu/>

            </nav>

        </div>


        <div class="flex flex-col min-h-screen lg:ml-72">

                {{-- ambiente de execução da aplicação --}}
                @production
                @else

                    <x-feedback.ambiente />

                @endproduction


                {{-- será exibido quando houver simulação de uso em execução --}}
                @if(session()->has('simulador'))

                    <x-feedback.simulacao />

                @endif


                {{-- conteúdo principal --}}
                <main class="flex-grow flex flex-col lg:px-6">

                    {{ $slot }}

                </main>


                <x-footer/>

        </div>


        {{-- caixa de mensagem para retorno ao usuário --}}
        <x-feedback.notificacao />


        {{-- javascript --}}
        @livewireScripts
        <script src="{{ mix('/js/app.js') }}"></script>
        @stack('scripts')

    </body>

</html>
