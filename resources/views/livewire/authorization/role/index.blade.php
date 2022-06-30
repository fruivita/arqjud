{{--
    View livewire for listing the roles.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Roles and permissions')">

    <x-search
        wire:key="search"
        wire:model.debounce.500ms="term"
        :error="$errors->first('term')"
        withcounter/>


    <x-container>

        <x-table.model.roles
            :limit="$this->limit"
            :preferencias="$this->preferencias"
            :roles="$this->roles"
            :sorts="$this->sorts"/>

    </x-container>

</x-page>
