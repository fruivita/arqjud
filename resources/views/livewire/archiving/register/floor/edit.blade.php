{{--
    View livewire for individual editing of floors.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the floor')">

    <x-backtrace :model="$floor"/>


    <x-container class="space-y-6">

        <form wire:key="form-floor" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="floor-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="floor.number"
                    wire:target="update"
                    :error="$errors->first('floor.number')"
                    icon="layers"
                    min="-100"
                    max="300"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Floor')"
                    :title="__('Inform the floor number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="floor-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="floor.description"
                    wire:target="update"
                    :error="$errors->first('floor.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the floor')"
                    :text="__('Description')"
                    :title="__('Describes the floor')"
                    withcounter/>


                {{-- Site --}}
                <x-form.select
                    wire:key="site"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model="site_id"
                    wire:target="site_id,update"
                    :error="$errors->first('site_id')"
                    icon="pin-map"
                    required
                    :text="__('Site')"
                    :title="__('Choose site')">

                    <option value="">{{ __('Select...') }}</option>


                    @forelse ($sites ?? [] as $site)

                        <option value="{{ $site->id }}">

                            {{ $site->name }}

                        </option>

                    @empty

                        <option value="-1">{{ __('No record found') }}</option>

                    @endforelse

                </x-form.select>


                {{-- Building --}}
                <div>

                    @if($site_id >= 1)

                        <x-form.select
                            wire:key="buildings-{{ $site_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="floor.building_id"
                            wire:target="site_id,update"
                            :error="$errors->first('floor.building_id')"
                            icon="building"
                            required
                            :text="__('Building')"
                            :title="__('Choose building')">

                            <option value="">{{ __('Select...') }}</option>

                            @forelse ($buildings ?? [] as $building)

                                <option value="{{ $building->id }}">

                                    {{ $building->name }}

                                </option>

                            @empty

                                <option value="-1">{{ __('No record found') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>


                <x-button-group>

                    <x-feedback.inline/>


                    <x-button
                        class="btn-do"
                        icon="save"
                        :text="__('Save')"
                        :title="__('Save the record')"
                        type="submit"/>


                    <x-link-button
                        class="btn-do"
                        icon="layers"
                        :href="route('archiving.register.floor.index')"
                        :text="__('Floors')"
                        :title="__('Show all records')"/>

                </x-button-group>

            </div>

        </form>


        <div class="overflow-x-auto">

            <x-perpage
                wire:key="per-page"
                wire:model="per_page"
                class="mb-3"
                :error="$errors->first('per_page')"/>


            <x-table wire:key="table-rooms" wire:loading.delay.class="opacity-25">

                <x-slot name="head">

                    <x-table.heading>{{ __('Room') }}</x-table.heading>


                    <x-table.heading>{{ __('Qty of boxes') }}</x-table.heading>


                    <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                </x-slot>


                <x-slot name="body">

                    @forelse ( $rooms ?? [] as $room )

                        <x-table.row>

                            <x-table.cell>{{ $room->number }}</x-table.cell>


                            <x-table.cell>{{ $room->boxes_count }}</x-table.cell>


                            <x-table.cell>

                                <x-action-button-group>

                                    @can(\App\Enums\Policy::View->value, \App\Models\Room::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="eye"
                                            :href="route('archiving.register.room.show', $room)"
                                            :text="__('Show')"
                                            :title="__('Show the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Update->value, \App\Models\Room::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="pencil-square"
                                            :href="route('archiving.register.room.edit', $room)"
                                            :text="__('Edit')"
                                            :title="__('Edit the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Delete->value, $room)

                                        <x-button
                                            wire:click="markToDelete({{ $room->id }})"
                                            wire:key="btn-delete-{{ $room->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            class="btn-danger w-full"
                                            icon="trash"
                                            :text="__('Delete')"
                                            :title="__('Delete the record')"
                                            type="button"/>

                                    @endcan

                                </x-action-button-group>

                            </x-table.cell>

                        </x-table.row>

                    @empty

                        <x-table.row>

                            <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                        </x-table.row>

                    @endforelse

                </x-slot>

            </x-table>

        </div>

    </x-container>


    {{ $rooms->links() }}


    @can(\App\Enums\Policy::Delete->value, $deleting)

        {{-- Modal to confirm the deletion --}}
        <x-confirmation-modal
            wire:model="show_delete_modal"
            wire:key="deleting-modal-{{ $deleting->id }}"
            wire:submit.prevent="destroy"
            :question="__('Delete :attribute?', ['attribute' => $deleting->number])"/>

    @endcan

</x-page>
