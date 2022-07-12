{{--
    Célula ocultável da tabela (table cell).

    A ocultação se dá por escolha do usuário que pode escolher quais colunas
    deseja ocultar.

    Props:
    - exibir: boolean se o célula deve ser exibida ou não. Útil para ocultar ou
    exibir uma determinada coluna inteira.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['exibir' => true])


@if ($exibir === true)

    <td {{ $attributes->merge(['class' => 'p-3']) }}>

        {{ $slot }}

    </td>

@endif
