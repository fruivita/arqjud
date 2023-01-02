<?php

namespace App\Http\Requests\Api\Solicitacao;

use App\Enums\Policy;
use App\Models\Solicitacao;
use App\Rules\UsuarioHabilitado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class ShowSolicitanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Create->value, Solicitacao::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'solicitante' => [
                'bail',
                'required',
                'string',
                'between:1,20',
                Rule::exists('usuarios', 'username'),
                new UsuarioHabilitado(),
            ],
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
            'solicitante' => __('Solicitante'),
        ];
    }
}
