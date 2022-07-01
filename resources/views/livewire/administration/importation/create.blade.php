{{--
    View livewire to perform forced data import.

    Forced import occurs via user request. It is of the forced type, because
    the application has a daily data import routine, making it unnecessary to
    force the import.
    However, in certain scenarios, it proves to be useful.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Forced data import')">

    <x-container>

        <div class="space-y-6">

            <h6 class="font-bold">{{ __('Import') }}</h6>


            <x-form.checkbox
                wire:key="checkbox-corporate"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:target="store"
                wire:model.defer="import"
                editavel
                name="import"
                :text="\App\Enums\ImportationType::Corporate->label()"
                :value="\App\Enums\ImportationType::Corporate->value"/>


            @error('import')

                <x-error>{{ $message }}</x-error>

            @enderror


            <x-button
                wire:click="store"
                wire:key="btn-store"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                class="btn-do"
                icon="play-circle"
                :text="__('Execute')"
                :title="__('Performs forced import of data')"
                type="button"/>

        </div>

    </x-container>

</x-page>
