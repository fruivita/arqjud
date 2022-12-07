<?php

namespace App\Http\Requests\Cadastro\Predio;

use App\Enums\Policy;
use App\Models\Predio;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostPredioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->predio)
            ? auth()->user()->can(Policy::Update->value, Predio::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Predio::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'localidade_id' => [
                'bail',
                isset($this->predio)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:localidades,id',
            ],

            'nome' => [
                'bail',
                'required',
                'string',
                'between:1,100',
                isset($this->predio)
                    ? "unique:predios,nome,{$this->predio->id},id,localidade_id,{$this->predio->localidade_id}" // PATCH OR PUT
                    : "unique:predios,nome,null,id,localidade_id,{$this->localidade_id}", // POST
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
            'localidade_id' => __('Localidade'),
            'nome' => __('Nome'),
            'descricao' => __('Descrição'),
        ];
    }
}
