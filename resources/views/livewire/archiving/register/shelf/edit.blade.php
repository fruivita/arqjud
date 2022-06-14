{{--
    View livewire for individual editing of shelves.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the shelf')">

    <x-backtrace :model="$shelf"/>


    <x-container>

        <form wire:key="form-shelf" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="shelf-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.number"
                    wire:target="update"
                    :error="$errors->first('shelf.number')"
                    icon="list-nested"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Shelf')"
                    :title="__('Inform the shelf number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="shelf-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.description"
                    wire:target="update"
                    :error="$errors->first('shelf.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the shelf')"
                    :text="__('Description')"
                    :title="__('Describes the shelf')"
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
                            wire:model="building_id"
                            wire:target="building_id,site_id,update"
                            :error="$errors->first('building_id')"
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


                {{-- Floor --}}
                <div>

                    @if($building_id >= 1)

                        <x-form.select
                            wire:key="floors-{{ $building_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="floor_id"
                            wire:target="building_id,site_id,update"
                            :error="$errors->first('floor_id')"
                            icon="layers"
                            required
                            :text="__('Floor')"
                            :title="__('Choose floor')">

                            <option value="">{{ __('Select...') }}</option>

                            @forelse ($floors ?? [] as $floor)

                                <option value="{{ $floor->id }}">

                                    {{ $floor->number }}

                                </option>

                            @empty

                                <option value="-1">{{ __('No record found') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>


                {{-- Room --}}
                <div>

                    @if($floor_id >= 1)

                        <x-form.select
                            wire:key="rooms-{{ $floor_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="room_id"
                            wire:target="floor_id,building_id,site_id,update"
                            :error="$errors->first('room_id')"
                            icon="door-closed"
                            required
                            :text="__('Room')"
                            :title="__('Choose room')">

                            <option value="">{{ __('Select...') }}</option>

                            @forelse ($rooms ?? [] as $room)

                                <option value="{{ $room->id }}">

                                    {{ $room->number }}

                                </option>

                            @empty

                                <option value="-1">{{ __('No record found') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>


                {{-- Stand --}}
                <div>

                    @if($room_id >= 1)

                        <x-form.select
                            wire:key="stands-{{ $room_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="shelf.stand_id"
                            wire:target="building_id,floor_id,room_id,site_id,update"
                            :error="$errors->first('shelf.stand_id')"
                            icon="bookshelf"
                            required
                            :text="__('Stand')"
                            :title="__('Choose stand')">

                            <option value="">{{ __('Select...') }}</option>

                            @forelse ($stands ?? [] as $stand)

                                <option value="{{ $stand->id }}">

                                    {{ $stand->numberForHumans() }}

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

                </x-button-group>

            </div>

        </form>

    </x-container>


    <x-container>

        <div class="overflow-x-auto">

            <div class="flex items-center justify-between mb-3">

                @can(\App\Enums\Policy::Create->value, \App\Models\Box::class)

                    <x-link-button
                        class="btn-do"
                        icon="plus-circle"
                        :href="route('archiving.register.box.create', $shelf)"
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


            <x-table wire:key="table-boxes" wire:loading.delay.class="opacity-25">

                <x-slot name="head">

                    <x-table.heading>{{ __('Box') }}</x-table.heading>


                    <x-table.heading>{{ __('Qty of volumes') }}</x-table.heading>


                    <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                </x-slot>


                <x-slot name="body">

                    @forelse ( $boxes ?? [] as $box )

                        <x-table.row>

                            <x-table.cell>{{ $box->number }}</x-table.cell>


                            <x-table.cell>{{ $box->volumes_count }}</x-table.cell>


                            <x-table.cell>

                                <x-action-button-group>

                                    @can(\App\Enums\Policy::View->value, \App\Models\Box::class)

                                        <x-icon-link-button
                                            class="btn-do"
                                            icon="eye"
                                            :href="route('archiving.register.box.show', $box)"
                                            :title="__('Show the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                                        <x-icon-link-button
                                            class="btn-do"
                                            icon="pencil-square"
                                            :href="route('archiving.register.box.edit', $box)"
                                            :title="__('Edit the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Delete->value, \App\Models\Box::class)

                                        <x-icon-link-button
                                            class="btn-danger"
                                            icon="pencil-square"
                                            {{-- href="{{ route('authorization.permission.edit', $permission) }}" --}}
                                            :title="__('Delete the record')"/>

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


    {{ $boxes->links() }}

</x-page>
