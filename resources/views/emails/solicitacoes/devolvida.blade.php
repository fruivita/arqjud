{{--
    Markdown de template do e-mail de solicitação de processos devolvido ao
    arquivo.

    @see https://laravel.com/docs/9.x/blade
    @see https://laravel.com/docs/9.x/mail
--}}
<x-mail::message>
# {{ __('Processo devolvido ao arquivo') }}

@unless(\App::environment('production'))
<x-mail::panel>
{{ __('Email sem valor. Disparado para fins de testes e homologação.') }}
</x-mail::panel>
@endunless

<x-mail::table>
| {{ __('Processo') }} | {{ __('Devolvido em') }} |
|:---------------------|:-------------------------|
| {{ cnj($detalhes->get('processo')) }} | {{ $detalhes->get('devolvido_em') }} |
</x-mail::table>

<x-mail::button :url="$detalhes->get('url')" color="success">
{{ __('Solicitações') }}
</x-mail::button>
</x-mail::message>
