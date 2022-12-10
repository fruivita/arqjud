<?php

namespace App\Http\Requests\Cadastro\Andar;

use App\Enums\Policy;
use App\Models\Andar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class UpdateAndarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Update->value, Andar::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'numero' => [
                'bail',
                'required',
                'integer',
                'between:-100,300',
                Rule::unique('andares', 'numero')
                    ->where('predio_id', $this->andar->predio_id)
                    ->ignore($this->andar),
            ],

            'apelido' => [
                'bail',
                'nullable',
                'string',
                'between:1,100',
                Rule::unique('andares', 'apelido')
                    ->where('predio_id', $this->andar->predio_id)
                    ->ignore($this->andar),
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
            'numero' => __('Número'),
            'apelido' => __('Apelido'),
            'descricao' => __('Descrição'),
        ];
    }
}
