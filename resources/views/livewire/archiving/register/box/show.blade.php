{{--
    View livewire for individual box display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Box') . ': ' . $box->name() }}">

    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    href="{{ route('archiving.register.box.show', $previous) }}"
                    prepend="true"
                    text="{{ __('Previous') }}"
                    title="{{ __('Show previous record') }}"/>

            @else

              <div></div>

            @endisset


            @isset($next)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-right"
                    href="{{ route('archiving.register.box.show', $next) }}"
                    text="{{ __('Next') }}"
                    title="{{ __('Show next record') }}"/>

            @else

                <div></div>

            @endisset

        </div>


        <div class="space-y-6">

            <x-show-value
                key="{{ __('Number') }}"
                value="{{ $box->number }}"/>


            <x-show-value
                key="{{ __('Year') }}"
                value="{{ $box->year }}"/>


            <x-show-value
                key="{{ __('Site') }}"
                :value="$box->room->floor->building->site->name"/>


            <x-show-value
                key="{{ __('Building') }}"
                :value="$box->room->floor->building->name"/>


            <x-show-value
                key="{{ __('Floor') }}"
                value="{{ $box->room->floor->number }}"/>


            <x-show-value
                key="{{ __('Room') }}"
                value="{{ $box->room->number }}"/>


            <x-show-value
                key="{{ __('Stand') }}"
                value="{{ $box->stand }}"/>


            <x-show-value
                key="{{ __('Shelf') }}"
                value="{{ $box->shelf }}"/>


            <x-show-value
                key="{{ __('Volumes') }}"
                value="{{ $box->volumes_count }}"/>


            <div class="flex flex-col space-x-0 space-y-3 lg:flex-row lg:items-center lg:justify-end lg:space-x-3 lg:space-y-0">

                @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        href="{{ route('archiving.register.box.edit', $box) }}"
                        text="{{ __('Edit') }}"
                        title="{{ __('Edit the record') }}"/>

                @endcan


                <x-link-button
                    class="btn-do"
                    icon="box2"
                    href="{{ route('archiving.register.box.index') }}"
                    text="{{ __('Boxes') }}"
                    title="{{ __('Show all records') }}"/>

            </div>

        </div>

    </x-container>

</x-page>
