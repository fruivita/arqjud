{{--
    View livewire for individual site display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Site') . ': ' . $this->site->name">

    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Site')"
                :value="$this->site->name"/>


            <x-show-value
                :key="__('Description')"
                :value="$this->site->description"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Site::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.site.edit', $this->site)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.building
            :buildings="$this->buildings"
            :parent="$this->site"
            :sorts="$this->sorts"
            withnewbutton/>

    </x-container>

</x-page>
