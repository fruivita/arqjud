<?php

namespace App\Http\Requests\Cadastro\Predio;

use App\Enums\Policy;
use App\Models\Predio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class UpdatePredioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Update->value, Predio::class);
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
                Rule::unique('predios', 'nome')
                    ->where('localidade_id', $this->predio->localidade_id)
                    ->ignore($this->predio),
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
