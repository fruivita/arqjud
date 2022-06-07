{{--
    View livewire for individual editing of buildings.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the building')">

    <x-backtrace :model="$building"/>


    <x-container>

        <form wire:key="form-building" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="building-name"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.name"
                    wire:target="update"
                    :error="$errors->first('building.name')"
                    icon="pin-map"
                    maxlength="100"
                    :placeholder="__('Building name')"
                    required
                    :text="__('Building')"
                    :title="__('Inform the building name')"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="building-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.description"
                    wire:target="update"
                    :error="$errors->first('building.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the building')"
                    :text="__('Description')"
                    :title="__('Describes the building')"
                    withcounter/>


                <x-form.select
                    wire:key="building-site"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.site_id"
                    wire:target="update"
                    :error="$errors->first('building.site_id')"
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


                <x-button-group>

                    <x-feedback.inline/>


                    <x-button
                        class="btn-do"
                        icon="save"
                        :text="__('Save')"
                        :title="__('Save the record')"
                        type="submit"/>

                </x-button-group>

            </div>

        </form>

    </x-container>


    <x-container>

        <div class="overflow-x-auto">

            <div class="flex items-center justify-between mb-3">

                @can(\App\Enums\Policy::Create->value, \App\Models\Floor::class)

                    <x-link-button
                        class="btn-do"
                        icon="plus-circle"
                        :href="route('archiving.register.floor.create', $building)"
                        :text="__('New')"
                        :title="__('Create a new record')"/>

                @else

                    <div></div>

                @endcan


                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    :error="$errors->first('per_page')"/>

            </div>


            <x-table wire:key="table-floors" wire:loading.delay.class="opacity-25">

                <x-slot name="head">

                    <x-table.heading>{{ __('Floor') }}</x-table.heading>


                    <x-table.heading>{{ __('Qty of rooms') }}</x-table.heading>


                    <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                </x-slot>


                <x-slot name="body">

                    @forelse ( $floors ?? [] as $floor )

                        <x-table.row>

                            <x-table.cell>{{ $floor->number }}</x-table.cell>


                            <x-table.cell>{{ $floor->rooms_count }}</x-table.cell>


                            <x-table.cell>

                                <x-action-button-group>

                                    @can(\App\Enums\Policy::View->value, \App\Models\Floor::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="eye"
                                            :href="route('archiving.register.floor.show', $floor)"
                                            :text="__('Show')"
                                            :title="__('Show the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Update->value, \App\Models\Floor::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="pencil-square"
                                            :href="route('archiving.register.floor.edit', $floor)"
                                            :text="__('Edit')"
                                            :title="__('Edit the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Delete->value, $floor)

                                        <x-button
                                            wire:click="markToDelete({{ $floor->id }})"
                                            wire:key="btn-delete-{{ $floor->id }}"
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


    {{ $floors->links() }}


    @can(\App\Enums\Policy::Delete->value, $deleting)

        {{-- Modal to confirm the deletion --}}
        <x-confirmation-modal
            wire:model="show_delete_modal"
            wire:key="deleting-modal-{{ $deleting->id }}"
            wire:submit.prevent="destroy"
            :question="__('Delete :attribute?', ['attribute' => $deleting->number])"/>

    @endcan

</x-page>
