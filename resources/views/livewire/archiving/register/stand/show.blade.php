{{--
    View livewire for individual stand display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Stand') . ': ' . $stand->numberForHumans()">

    <x-backtrace :model="$stand"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Stand')"
                :value="$stand->numberForHumans()"/>


            <x-show-value
                :key="__('Description')"
                :value="$stand->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$stand->room->floor->building->site->name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$stand->room->floor->building->name"/>

            </div>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2">

                <x-show-value
                    :key="__('Floor')"
                    :value="$stand->room->floor->number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$stand->room->number"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Stand::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.stand.edit', $stand)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.index.shelf
            :shelves="$shelves"
            :parent="$stand"
            withnewbutton/>

    </x-container>

</x-page>
