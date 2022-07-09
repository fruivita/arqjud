{{--
    Botões para habilitar/desabilitar a edição, cancelar a edição ou submeter
    o formulário.

    Props:
    - modo_edicao: botões que serão exibidos no formulário.
        False: exibe o botão 'Editar'
        True: exibe o botão 'Salvar' e 'Cancelar'

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['modo_edicao'])


{{-- Mensagem de retorno a respeito da ação executada --}}
<x-feedback.inline/>


@if ($modo_edicao)

    <x-button
        wire:click="update"
        wire:key="btn-grupo-update"
        wire:loading.delay.attr="disabled"
        wire:loading.delay.class="cursor-not-allowed"
        class="btn-acao"
        icone="save"
        :texto="__('Salvar')"
        :title="__('Salvar o registro')"
        type="button"/>


    <x-button
        wire:click="$set('modo_edicao', false)"
        wire:key="btn-grupo-cancelar"
        wire:loading.delay.attr="disabled"
        wire:loading.delay.class="cursor-not-allowed"
        wire:target="update"
        class="btn-cancelar"
        icone="x-circle"
        :texto="__('Cancelar')"
        :title="__('Cancelar a operação')"
        type="button"/>

@else

    <x-button
        wire:click="$set('modo_edicao', true)"
        wire:key="btn-grupo-editar"
        wire:loading.delay.attr="disabled"
        wire:loading.delay.class="cursor-not-allowed"
        class="btn-acao-alternativo"
        icone="pencil-square"
        :texto="__('Editar')"
        :title="__('Editar o registro')"
        type="button"/>

@endif
