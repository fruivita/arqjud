{{--
    Markdown de template do e-mail de solicitação de processos cancelada.

    @see https://laravel.com/docs/9.x/blade
    @see https://laravel.com/docs/9.x/mail
--}}
<x-mail::message>
# {{ __('Solicitação de processo cancelada') }}

@unless(\App::environment('production'))
<x-mail::panel>
{{ __('Email sem valor. Disparado para fins de testes e homologação.') }}
</x-mail::panel>
@endunless

{{ __('Cancelada por :attribute1 em :attribute2', ['attribute1' => $detalhes->get('operador'), 'attribute2' => $detalhes->get('cancelada_em')]) }}

<x-mail::table>
| {{ __('Processo') }} | {{ __('Solicitante') }} | {{ __('Lotação destinatária') }} | {{ __('Solicitada em') }} |
|:---------------------|:------------------------|:---------------------------------|:--------------------------|
| {{ $detalhes->get('processo') }} | {{ $detalhes->get('solicitante') }} | {{ $detalhes->get('lotacao_destinataria') }} | {{ $detalhes->get('solicitada_em') }} |
</x-mail::table>

<x-mail::button :url="$detalhes->get('url')" color="success">
{{ __('Solicitações') }}
</x-mail::button>
</x-mail::message>
