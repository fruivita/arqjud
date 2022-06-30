{{--
    View livewire for listing stands.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Stands')">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <x-table.model.stand
            :deleting="$this->deleting"
            :preferencias="$this->preferencias"
            :stands="$this->stands"
            :sorts="$this->sorts"
            withdeletebutton
            withparents/>

    </x-container>

</x-page>
