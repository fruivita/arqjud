{{--
    View livewire for individual creation of stand.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New stands')">

    <x-backtrace :model="$room" :root="true"/>


    <x-container>

        <form wire:key="form-stand" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-show-value
                    :key="__('Site')"
                    :value="$room->floor->building->site->name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$room->floor->building->name"/>


                <x-show-value
                    :key="__('Floor')"
                    :value="$room->floor->number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$room->number"/>


                <x-form.input
                    wire:key="stand-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="stand.number"
                    wire:target="store"
                    :error="$errors->first('stand.number')"
                    icon="bookshelf"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Stand')"
                    :title="__('Inform the stand number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="stand-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="stand.description"
                    wire:target="store"
                    :error="$errors->first('stand.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the stand')"
                    :text="__('Description')"
                    :title="__('Describes the stand')"
                    withcounter/>


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

        <x-perpage
            wire:key="per-page"
            wire:model="per_page"
            class="mb-3"
            :error="$errors->first('per_page')"/>


        <x-table wire:key="table-stands" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading>{{ __('Stand') }}</x-table.heading>


                <x-table.heading>{{ __('Qty of shelves') }}</x-table.heading>


                <x-table.heading>{{ __('Site') }}</x-table.heading>


                <x-table.heading>{{ __('Building') }}</x-table.heading>


                <x-table.heading>{{ __('Floor') }}</x-table.heading>


                <x-table.heading>{{ __('Room') }}</x-table.heading>


                <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($stands ?? [] as $stand)

                    <x-table.row>

                        <x-table.cell>{{ $stand->number }}</x-table.cell>


                        <x-table.cell>{{ $stand->shelves_count }}</x-table.cell>


                        <x-table.cell>{{ $stand->room->floor->building->site->name }}</x-table.cell>


                        <x-table.cell>{{ $stand->room->floor->building->name }}</x-table.cell>


                        <x-table.cell>{{ $stand->room->floor->number }}</x-table.cell>


                        <x-table.cell>{{ $stand->room->number }}</x-table.cell>


                        <x-table.cell>

                            <x-action-button-group>

                                @can(\App\Enums\Policy::View->value, \App\Models\Stand::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.stand.show', $stand)"
                                        :text="__('Show')"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Update->value, \App\Models\Stand::class)

                                    <x-link-button
                                        class="btn-do"
                                        icon="pencil-square"
                                        :href="route('archiving.register.stand.edit', $stand)"
                                        :text="__('Edit')"
                                        :title="__('Edit the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, $stand)

                                    <x-button
                                        wire:click="markToDelete({{ $stand->id }})"
                                        wire:key="btn-delete-{{ $stand->id }}"
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

                        <x-table.cell colspan="7">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </x-container>


    {{ $stands->links() }}


    @can(\App\Enums\Policy::Delete->value, $deleting)

        {{-- Modal to confirm the deletion --}}
        <x-confirmation-modal
            wire:model="show_delete_modal"
            wire:key="deleting-modal-{{ $deleting->id }}"
            wire:submit.prevent="destroy"
            :question="__('Delete :attribute?', ['attribute' => $deleting->number])"/>

    @endcan

</x-page>
