{{--
    Default form for search.

     Props:
    - error: error message that will be displayed
    - withcounter: whether to display the counter of typed characters

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['error' => null, 'withcounter' => false])


<div
    @if ($withcounter) x-data="{ counter: 0, visivel: false }"@endif
    class="text-primary-900 dark:text-secondary-50 md:mx-auto md:w-2/4"
>

    <div class="bg-primary-100 border-2 border-primary-300 flex items-center pl-2 py-2 pr-6 rounded dark:bg-secondary-800 dark:border-secondary-600">

        <label class="p-2" for="term">

            <x-icon name="search"/>

        </label>


        <input

            @if ($withcounter)

                x-on:blur="visivel = false"
                x-on:focus="visivel = true"
                x-on:keyup="counter = $el.value.length"
                x-ref="message"

            @endif

            autocomplete="off"
            autofocus
            class="bg-primary-100 flex-1 outline-none px-4 py-2 truncate dark:bg-secondary-800"
            id="term"
            maxlength="50"
            placeholder="{{ __('Searchable term') }}"
            type="text"
            {{ $attributes }} />


        {{-- eventual display of character counter --}}
        @if ($withcounter)

            <span
                x-show="counter && visivel"
                class="text-right text-primary-500 text-sm whitespace-nowrap dark:text-secondary-500"
            >

                <span x-text="counter + ' / ' + $refs.message.maxLength"></span>

            </span>

        @endif

    </div>


    {{-- display of any error message --}}
    <x-error>{{ $error }}</x-error>

</div>
