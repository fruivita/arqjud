{{--
    View livewire for individual stand display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Stand') . ': ' . $this->stand->for_humans">

    <x-backtrace :model="$this->stand"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Stand')"
                :value="$this->stand->for_humans"/>


            <x-show-value
                :key="__('Description')"
                :value="$this->stand->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$this->stand->site_name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$this->stand->building_name"/>

            </div>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2">

                <x-show-value
                    :key="__('Floor')"
                    :value="$this->stand->floor_number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$this->stand->room_number"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Stand::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.stand.edit', $this->stand->id)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.shelf
            :parent="$this->stand"
            :shelves="$this->shelves"
            :sorts="$this->sorts"
            withnewbutton/>

    </x-container>

</x-page>
