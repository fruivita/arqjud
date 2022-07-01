{{--
    Default confirmation modal.

    Props:
    - question: question for user confirmation.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['question'])


<x-modal {{ $attributes->whereDoesntStartWith('wire:click') }}>

    <x-slot name="title">{{ $question }}</x-slot>


    <x-slot name="content">{{ __('This operation is irreversible. Are you sure you wish to proceed?') }}</x-slot>


    <x-slot name="footer">

        <div>

            <x-button
                {{ $attributes->whereStartsWith('wire:click') }}
                class="btn-danger w-full"
                icon="check-circle"
                :text="__('Confirm')"
                :title="__('Confirm the operation')"
                type="button"/>

        </div>

    </x-slot>

</x-modal>
