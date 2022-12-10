<?php

namespace App\Http\Requests\Cadastro\Prateleira;

use App\Enums\Policy;
use App\Models\Prateleira;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class UpdatePrateleiraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Update->value, Prateleira::class);
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
                'string',
                'between:1,50',
                Rule::unique('prateleiras', 'numero')
                    ->where('estante_id', $this->prateleira->estante_id)
                    ->ignore($this->prateleira),
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
            'descricao' => __('Descrição'),
        ];
    }
}
