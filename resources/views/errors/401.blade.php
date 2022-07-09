{{--
    View para erro HTTP 401.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/9.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('titulo', __('error.401.titulo'))
@section('codigo', '401')
@section('mensagem', __('error.401.mensagem'))
