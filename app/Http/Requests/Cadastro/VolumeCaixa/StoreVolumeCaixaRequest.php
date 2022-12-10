<?php

namespace App\Http\Requests\Cadastro\VolumeCaixa;

use App\Enums\Policy;
use App\Models\VolumeCaixa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class StoreVolumeCaixaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Create->value, VolumeCaixa::class);
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
                'between:1,9999',
                Rule::unique('volumes_caixa', 'numero')
                    ->where('caixa_id', $this->caixa->id),
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
