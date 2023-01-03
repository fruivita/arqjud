<?php

namespace App\Http\Requests\Administracao;

use App\Enums\Policy;
use App\Models\Perfil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
 */
class StorePerfilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Create->value, Perfil::class);
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
                Rule::unique('perfis', 'nome'),
            ],

            'slug' => [
                'bail',
                'required',
                'string',
                'between:1,50',
                Rule::unique('perfis', 'slug'),
            ],

            'poder' => [
                'bail',
                'required',
                'integer',
                'between:1001,9998', // 1000 (Padrão) e 9999 (Administrador)
                Rule::unique('perfis', 'poder'),
            ],

            'descricao' => [
                'bail',
                'nullable',
                'string',
                'between:1,255',
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
            'slug' => __('Slug'),
            'poder' => __('Poder'),
            'descricao' => __('Descrição'),
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $merge = [];

        if ($this->has('nome')) {
            $merge['slug'] = str($this->get('nome'))->slug()->toString();
        }

        $this->merge($merge);
    }
}
