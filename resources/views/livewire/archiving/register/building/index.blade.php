{{--
    View livewire for listing buildings.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Buildings')">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <x-table.model.building
            :buildings="$this->buildings"
            :colunas="$this->colunas"
            :deleting="$this->deleting"
            :sorts="$this->sorts"
            withdeletebutton
            withparents/>

    </x-container>

</x-page>
