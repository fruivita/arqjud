<?php

namespace App\Http\Requests\Api\Caixa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class ShowCaixaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'localidade_criadora_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('localidades', 'id'),
            ],

            'ano' => [
                'bail',
                'required',
                'integer',
                'between:1900,' . now()->format('Y'),
            ],

            'guarda_permanente' => [
                'boolean',
            ],

            'complemento' => [
                'bail',
                'nullable',
                'string',
                'between:1,50',
            ],

            'numero' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('caixas', 'numero')
                    ->where('ano', $this->ano)
                    ->where('guarda_permanente', $this->guarda_permanente)
                    ->when(
                        $this->complemento,
                        function ($query, $complemento) {
                            return $query->where('complemento', $complemento);
                        },
                        function ($query) {
                            return $query->whereNull('complemento');
                        }
                    )
                    ->where('localidade_criadora_id', $this->localidade_criadora_id),
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
            'localidade_criadora_id' => __('Localidade criadora'),
            'ano' => __('Ano'),
            'guarda_permanente' => __('Guarda Permanente'),
            'complemento' => __('Complemento'),
            'numero' => __('Número'),
        ];
    }
}
