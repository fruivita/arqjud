{{--
    View livewire for individual box display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Box') . ': ' . $box->name()">

    <x-backtrace :model="$box"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Number')"
                :value="$box->number"/>


            <x-show-value
                :key="__('Year')"
                :value="$box->year"/>


            <x-show-value
                :key="__('Description')"
                :value="$box->description"/>


            <x-show-value
                :key="__('Qty of volumes')"
                :value="$box->volumes_count"/>


            <x-show-value
                :key="__('Site')"
                :value="$box->shelf->stand->room->floor->building->site->name"/>


            <x-show-value
                :key="__('Building')"
                :value="$box->shelf->stand->room->floor->building->name"/>


            <x-show-value
                :key="__('Floor')"
                :value="$box->shelf->stand->room->floor->number"/>


            <x-show-value
                :key="__('Room')"
                :value="$box->shelf->stand->room->number"/>


            <x-show-value
                :key="__('Stand')"
                :value="$box->shelf->stand->numberForHumans()"/>


            <x-show-value
                :key="__('Shelf')"
                :value="$box->shelf->numberForHumans()"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.box.edit', $box)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-volumes" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Volume') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $volumes ?? [] as $volume )

                            <x-table.row>

                                <x-table.cell>{{ $volume->number }}</x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="1">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>

        </div>

    </x-container>


    {{ $volumes->links() }}

</x-page>
