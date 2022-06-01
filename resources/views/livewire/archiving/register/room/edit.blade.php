{{--
    View livewire for individual editing of rooms.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the room')">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    :href="route('archiving.register.room.edit', $previous)"
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
                    :href="route('archiving.register.room.edit', $next)"
                    :text="__('Next')"
                    :title="__('Show next record')"/>

            @else

                <div></div>

            @endisset

        </div>


        <form wire:key="form-room" wire:submit.prevent="update" method="POST">

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


                {{-- Building --}}
                <div>

                    @if($site_id >= 1)

                        <x-form.select
                            wire:key="buildings-{{ $site_id }}"
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
                            wire:model.defer="room.floor_id"
                            :error="$errors->first('room.floor_id')"
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
                        icon="door-closed"
                        :href="route('archiving.register.room.index')"
                        :text="__('Rooms')"
                        :title="__('Show all records')"/>

                </x-button-group>

            </div>

        </form>

    </x-container>

</x-page>
