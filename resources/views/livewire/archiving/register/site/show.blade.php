{{--
    View livewire for individual site display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Site') . ': ' . $site->name">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    :href="route('archiving.register.site.show', $previous)"
                    prepend="true"
                    :text="__('Previous')"
                    :title="__('Show previous record')"/>

            @else

              <div></div>

            @endisset


            @isset($next)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-right"
                    :href="route('archiving.register.site.show', $next)"
                    :text="__('Next')"
                    :title="__('Show next record')"/>

            @else

                <div></div>

            @endisset

        </div>


        <div class="space-y-6">

            <x-show-value
                :key="__('Site')"
                :value="$site->name"/>


            <x-show-value
                :key="__('Description')"
                :value="$site->description"/>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-buildings" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Buildings') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $buildings ?? [] as $building )

                            <x-table.row>

                                <x-table.cell>{{ $building->name }}</x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="1">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Site::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.site.edit', $site)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan


                <x-link-button
                    class="btn-do"
                    icon="pin-map"
                    :href="route('archiving.register.site.index')"
                    :text="__('Sites')"
                    :title="__('Show all records')"/>

            </x-button-group>

        </div>

    </x-container>


    {{ $buildings->links() }}

</x-page>
