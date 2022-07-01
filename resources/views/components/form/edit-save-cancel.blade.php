{{--
    Botões para habilitar/desabilitar edição ou submeter o formulário.

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
        wire:key="btn-group-update"
        wire:loading.delay.attr="disabled"
        wire:loading.delay.class="cursor-not-allowed"
        class="btn-do"
        icon="save"
        :text="__('Save')"
        :title="__('Save the record')"
        type="button"/>


    <x-button
        wire:click="$set('modo_edicao', false)"
        wire:key="btn-group-cancel"
        wire:loading.delay.attr="disabled"
        wire:loading.delay.class="cursor-not-allowed"
        wire:target="update"
        class="btn-cancel"
        icon="x-circle"
        :text="__('Cancel')"
        :title="__('Cancel the operation')"
        type="button"/>

@else

    <x-button
        wire:click="$set('modo_edicao', true)"
        wire:key="btn-group-edit"
        wire:loading.delay.attr="disabled"
        wire:loading.delay.class="cursor-not-allowed"
        class="btn-do-alterative"
        icon="pencil-square"
        :text="__('Edit')"
        :title="__('Edit the record')"
        type="button"/>

@endif
