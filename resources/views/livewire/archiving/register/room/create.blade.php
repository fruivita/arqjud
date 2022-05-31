{{--
    View livewire for individual creation of room.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New rooms')">

    <x-container class="space-y-6">

        <form wire:key="form-room" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="room-number"
                    wire:model.defer="room.number"
                    :error="$errors->first('room.number')"
                    icon="layers"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Room')"
                    :title="__('Inform the room number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="room-description"
                    wire:model.defer="room.description"
                    :error="$errors->first('room.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the room')"
                    :text="__('Description')"
                    :title="__('Describes the room')"
                    withcounter/>


                {{-- Site --}}
                <div>

                    <x-form.select
                        wire:key="site"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model="site_id"
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

                </div>


                {{-- Building --}}
                <div>

                    @if($site_id >= 1)

                        <x-form.select
                            wire:key="buildings-{{ $site_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="building_id"
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
                            wire:model.defer="floor_id"
                            class="w-full"
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


                <div class="flex flex-col space-x-0 space-y-3 lg:flex-row lg:items-center lg:justify-end lg:space-x-3 lg:space-y-0">

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
                        :href="route('archiving.register.room.index')"
                        :text="__('Rooms')"
                        :title="__('Show all records')"/>

                </div>

            </div>

        </form>

    </x-container>

</x-page>
