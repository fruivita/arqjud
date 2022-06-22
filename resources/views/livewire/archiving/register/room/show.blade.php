{{--
    View livewire for individual room display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Room') . ': ' . $this->room->number">

    <x-backtrace :model="$this->room"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Room')"
                :value="$this->room->number"/>


            <x-show-value
                :key="__('Description')"
                :value="$this->room->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$this->room->site_name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$this->room->building_name"/>

            </div>


            <x-show-value
                :key="__('Floor')"
                :value="$this->room->floor_number"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Room::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.room.edit', $this->room->id)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.stand
            :parent="$this->room"
            :stands="$this->stands"
            :sorts="$this->sorts"
            withnewbutton/>

    </x-container>

</x-page>
