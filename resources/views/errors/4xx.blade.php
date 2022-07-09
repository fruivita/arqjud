{{--
    View para erro HTTP 4xx.

    Nota: Essa view é usada como view padrão para erros no range 400 ~ 499,
    isto é, para a hipótese de não haver view específica para exibir o erro.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/9.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('titulo', __('error.4xx.titulo'))
@section('codigo', $exception->getStatusCode())
@section('mensagem', $exception->getMessage() ?: __('error.4xx.mensagem'))
