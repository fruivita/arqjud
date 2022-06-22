{{--
    View livewire for individual floor display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Floor') . ': ' . $this->floor->number">

    <x-backtrace :model="$this->floor"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Floor')"
                :value="$this->floor->number"/>


            <x-show-value
                :key="__('Description')"
                :value="$this->floor->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$this->floor->site_name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$this->floor->building_name"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Floor::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.floor.edit', $this->floor->id)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.room
            :parent="$this->floor"
            :rooms="$this->rooms"
            :sorts="$this->sorts"
            withnewbutton/>

    </x-container>

</x-page>
