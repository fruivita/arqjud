{{--
    Trilha de links para navegação para as entidades pai do modelo informado.

    Props:
    - model: objeto a partir do qual serão gerados os links para os modelos pai
    - root: boolean deve incluir o próprio modelo ou apenas os pais

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['model', 'root' => false])


<div class="font-bold mx-3 text-right text-sm lg:mx-0">

    @foreach ($model->linksPais($root) ?? [] as $texto => $link)

        <a class="underline" href="{{ $link }}">{{ $texto }}</a>


        @unless ($loop->last)

            <span>></span>

        @endunless

    @endforeach

</div>
