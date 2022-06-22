{{--
    View livewire for listing sites.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Sites')">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <x-table.model.site
            :deleting="$this->deleting"
            :sites="$this->sites"
            :sorts="$this->sorts"
            withnewbutton/>

    </x-container>

</x-page>
