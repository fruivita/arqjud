<?php

namespace App\Http\Requests\Administracao;

use App\Enums\Policy;
use App\Models\Permissao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
 */
class UpdatePermissaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Update->value, Permissao::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nome' => [
                'bail',
                'required',
                'string',
                'between:1,50',
                Rule::unique('permissoes', 'nome')
                    ->ignore($this->permissao),
            ],

            'descricao' => [
                'bail',
                'nullable',
                'string',
                'between:1,255',
            ],

            'perfil_id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists('perfis', 'id'),
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
            'nome' => __('Nome'),
            'descricao' => __('Descrição'),
            'perfil_id' => __('Perfil'),
        ];
    }
}
