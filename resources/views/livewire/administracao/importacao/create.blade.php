{{--
    View livewire para excutar a importação forçada de dados.

    Importação forçada ocorre por meio da requisição do usuário. É do tipo
    forçada, pois a aplicação possui rotinas diárias para executá-la
    automaticamente tornando desnecessário forçar a importação.

    Contudo, em certos cenários, ela é útil.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Importação forçada de dados')">

    <x-container>

        <div class="space-y-6">

            <h6 class="font-bold">{{ __('Importar') }}</h6>


            <x-form.checkbox
                wire:key="checkbox-corporativo"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:target="store"
                wire:model.defer="importacoes"
                editavel
                name="import"
                :texto="\App\Enums\Importacao::Corporativo->nome()"
                :value="\App\Enums\Importacao::Corporativo->value"/>


            {{-- exibição de eventual mensagem de erro --}}
            @error('importacoes') <x-erro>{{ $message }}</x-erro> @enderror


            <x-button
                wire:click="store"
                wire:key="btn-store"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                class="btn-acao"
                icone="play-circle"
                :texto="__('Executar')"
                :title="__('Executa a importação forçada dos dados')"
                type="button"/>

        </div>

    </x-container>

</x-page>
