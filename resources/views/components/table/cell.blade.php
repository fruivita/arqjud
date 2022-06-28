{{--
    Célula ocultável da tabela (table cell).

    Props:
    - exibir: se o célula deve ser exibida ou não. Útil para ocultar/exibir uma
    determinada coluna inteira.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['exibir' => true])


@if ($exibir === true)

    <td
        {{ $attributes->merge(['class' => 'p-3']) }}
        {{ $attributes->except('class') }}
    >

        {{ $slot }}

    </td>

@endif
