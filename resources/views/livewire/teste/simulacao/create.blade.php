{{--
    View livewire para criar simulação de uso.

    Simulação é o ato de um usuário, geralmente um administrador, utilizar a
    aplicação como se fosse outro usúario.
    Útil para testar a aplicação vendo como ela se comporta através do prisma
    de outro usuário.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Simulação de uso da aplicação')">

    <x-container>

        <div class="space-y-6">

            <div class="lg:inline-flex">

                <x-form.input
                    wire:key="username"
                    wire:model.defer="username"
                    autocomplete="off"
                    autofocus
                    editavel
                    :erro="$errors->first('username')"
                    icone="people"
                    maxlength="20"
                    :placeholder="__('Usuário de rede')"
                    required
                    :texto="__('Usuário a ser simulado')"
                    :title="__('Informe um usuário de rede')"
                    type="text"
                    com_contador/>

            </div>


            <x-button
                wire:click="store"
                wire:key="btn-store"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                class="btn-acao"
                icone="play-circle"
                :texto="__('Simular')"
                :title="__('Simula o usuário informado')"
                type="button"/>

        </div>

    </x-container>

</x-page>
