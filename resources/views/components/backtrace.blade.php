{{--
    Backtrace links for navigation in the parent entities of the informed
    model.

    Props:
    - model: child model to generate links to its parents (backtrace links)
    - root: must include the root (child) model or only its parents

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['model', 'root' => false])


<div class="font-bold mx-3 text-right text-sm lg:mx-0">

    @foreach ($model->parentEntitiesLinks($root) ?? [] as $label => $link)

        <a class="underline" href="{{ $link }}">{{ $label }}</a>


        @unless ($loop->last)

            <span>></span>

        @endunless

    @endforeach

</div>
