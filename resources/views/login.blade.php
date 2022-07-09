{{--
    View de login.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
    @see https://www.chromium.org/developers/design-documents/create-amazing-password-forms
--}}


<x-layouts.app>

    <article class="flex items-center justify-center flex-grow">

        <x-container class="flex flex-col items-center justify-center rounded space-y-12">

            <h1 class="bg-primaria-500 flex font-extrabold items-center h-24 justify-center rounded-full text-primaria-50 w-24">{{ config('orgao.sigla') }}</h1>


            <form>

                @csrf


                <div class="flex flex-col p-3 space-y-6">

                    <x-form.input
                        autocomplete="username"
                        autofocus
                        editavel
                        :erro="$errors->first('username')"
                        icone="person"
                        id="username"
                        :placeholder="__('Usuário de rede')"
                        required
                        :texto="__('Usuário')"
                        :title="__('Informe seu usuário de rede')"
                        type="text"
                        :value="old('username')"/>


                    <x-form.input
                        autocomplete="current-password"
                        editavel
                        :erro="$errors->first('password')"
                        icone="key"
                        id="password"
                        :placeholder="__('Senha de rede')"
                        required
                        :texto="__('Senha')"
                        :title="__('Informe sua senha de rede')"
                        type="password"
                        value=""/>


                    <x-button
                        class="btn-padrao"
                        formaction="{{ route('login') }}"
                        formmethod="POST"
                        icone="box-arrow-in-right"
                        :texto="__('Login')"
                        :title="__('Entra na aplicação')"
                        type="submit"/>

                </div>

            </form>

        </x-container>

    </article>

</x-layouts.app>
