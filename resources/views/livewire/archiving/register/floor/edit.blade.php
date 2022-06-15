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


    <x-container>

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


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                    <div>

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

                    </div>


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

        <x-table.model.room
            :rooms="$rooms"
            :deleting="$deleting"
            :parent="$floor"
            withdeletebutton
            withnewbutton/>

    </x-container>

</x-page>
