{{--
    Ações para manipulação de múltiplos checkboxs em uma tabela.

    Usado para automatizar operações comuns como selecionar ou desmarcar todos.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<select
    title="{{ __('Ações de checkbox') }}"
    {{ $attributes->merge(['class' => "bg-primaria-300 rounded w-14 dark:bg-secundaria-500"]) }}
>

    <option value=""></option>


    @foreach (\App\Enums\AcaoCheckbox::cases() as $acao)

        <option value="{{ $acao->value }}">

            {{ $acao->nome() }}

        </option>

    @endforeach

</select>
