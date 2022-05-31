{{--
    View livewire for individual creation of building.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New buildings')">

    <x-container class="space-y-6">

        <form wire:key="form-building" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="building-name"
                    wire:model.defer="building.name"
                    :error="$errors->first('building.name')"
                    icon="building"
                    maxlength="100"
                    :placeholder="__('Building name')"
                    required
                    :text="__('Building')"
                    :title="__('Inform the building name')"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="building-description"
                    wire:model.defer="building.description"
                    :error="$errors->first('building.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the building')"
                    :text="__('Description')"
                    :title="__('Describes the building')"
                    withcounter/>


                <x-form.select
                    wire:key="site"
                    wire:model.defer="site_id"
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
                        icon="building"
                        :href="route('archiving.register.building.index')"
                        :text="__('Buildings')"
                        :title="__('Show all records')"/>

                </div>

            </div>

        </form>

    </x-container>

</x-page>
