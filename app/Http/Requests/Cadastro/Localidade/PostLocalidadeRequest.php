<?php

namespace App\Http\Requests\Cadastro\Localidade;

use App\Enums\Policy;
use App\Models\Localidade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostLocalidadeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->localidade)
            ? auth()->user()->can(Policy::Update->value, Localidade::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Localidade::class); // POST
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
                'between:1,100',
                isset($this->localidade)
                    ? "unique:localidades,nome,{$this->localidade->id}" // PATCH OR PUT
                    : 'unique:localidades,nome', // POST
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
            'descricao' => __('Descrição'),
        ];
    }
}
