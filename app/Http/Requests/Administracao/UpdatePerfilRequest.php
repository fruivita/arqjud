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
class UpdatePerfilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Update->value, Perfil::class);
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
                Rule::unique('perfis', 'nome')
                    ->ignore($this->perfil),
            ],

            'slug' => [
                'bail',
                'required',
                'string',
                'between:1,50',
                Rule::unique('perfis', 'slug')
                    ->ignore($this->perfil),
            ],

            'descricao' => [
                'bail',
                'nullable',
                'string',
                'between:1,255',
            ],

            'permissao_id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists('permissoes', 'id'),
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
            'descricao' => __('Descrição'),
            'permissao_id' => __('Permissão'),
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
