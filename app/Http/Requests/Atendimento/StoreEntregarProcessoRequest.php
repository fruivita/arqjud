<?php

namespace App\Http\Requests\Atendimento;

use App\Enums\Policy;
use App\Models\Solicitacao;
use App\Rules\PasswordValido;
use App\Rules\SolicitacaoEntregavel;
use App\Rules\UsuarioHabilitado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class StoreEntregarProcessoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Update->value, Solicitacao::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'recebedor' => [
                'bail',
                'required',
                'string',
                'between:1,20',
                Rule::exists('usuarios', 'username'),
                new UsuarioHabilitado(),
            ],

            'por_guia' => ['boolean'],

            'password' => [
                'bail',
                Rule::excludeIf($this->por_guia === true),
                'required',
                'string',
                'max:50',
                new PasswordValido($this->recebedor),
            ],

            'solicitacoes.*' => [
                'bail',
                'required',
                'integer',
                Rule::exists('solicitacoes', 'id'),
                new SolicitacaoEntregavel(),
            ],

            'email_terceiros.*' => ['nullable', 'email:strict'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'recebedor' => __('Recebedor'),
            'por_guia' => __('Entrega por guia'),
            'password' => __('Senha'),
            'solicitacoes.*' => __('Solicitação'),
            'email_terceiros.*' => __('Email'),
        ];
    }
}
