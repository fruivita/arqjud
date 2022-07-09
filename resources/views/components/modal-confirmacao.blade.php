{{--
    Modal padrão para confirmação de operações críticas.

    Props:
    - pergunta: string com a pergunta que deve ser feita ao usuário para a
    confirmação da operação..

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['pergunta'])


<x-modal {{ $attributes->whereDoesntStartWith('wire:click') }}>

    <x-slot name="titulo">{{ $pergunta }}</x-slot>


    <x-slot name="conteudo">{{ __('Essa operação é irreversível. Tem certeza que deseja continuar?') }}</x-slot>


    <x-slot name="rodape">

        <div>

            <x-button
                {{ $attributes->whereStartsWith('wire:click') }}
                class="btn-perigo w-full"
                icone="check-circle"
                :texto="__('Confirmar')"
                :title="__('Confirmar a operação')"
                type="button"/>

        </div>

    </x-slot>

</x-modal>
