{{--
    Markdown de template do e-mail de solicitação de processos criada.

    @see https://laravel.com/docs/9.x/blade
    @see https://laravel.com/docs/9.x/mail
--}}
<x-mail::message>
# {{ __('Solicitação de processos criada') }}

@unless(\App::environment('production'))
<x-mail::panel>
{{ __('Email sem valor. Disparado para fins de testes e homologação.') }}
</x-mail::panel>
@endunless

<x-mail::table>
| {{ __('Solicitante') }} | {{ __('Destino') }} | {{ __('Solicitada em') }} |
|:------------------------|:---------------------------------|:--------------------------|
| {{ $detalhes->get('solicitante') }} | {{ $detalhes->get('destino') }} | {{ $detalhes->get('solicitada_em') }} |
</x-mail::table>

@foreach ($detalhes->get('processos') as $processo)
- {{ cnj($processo) }}
@endforeach

<x-mail::button :url="$detalhes->get('url')" color="success">
{{ __('Solicitações') }}
</x-mail::button>
</x-mail::message>
