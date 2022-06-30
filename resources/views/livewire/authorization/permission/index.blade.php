{{--
    View livewire for permissions listing.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Permissions and roles')">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <x-table.model.permissions
            :limit="$this->limit"
            :preferencias="$this->preferencias"
            :permissions="$this->permissions"
            :sorts="$this->sorts"/>

    </x-container>

</x-page>
