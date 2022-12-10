<?php

namespace App\Http\Requests\Cadastro\Caixa;

use App\Enums\Policy;
use App\Models\Caixa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class UpdateCaixaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Update->value, Caixa::class);
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

            'numero' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::unique('caixas', 'numero')
                    ->where('ano', $this->ano)
                    ->where('guarda_permanente', $this->guarda_permanente)
                    ->where('localidade_criadora_id', $this->localidade_criadora_id)
                    ->where('prateleira_id', $this->caixa->prateleira_id)
                    ->when($this->complemento, fn ($query) => $query->where('complemento', $this->complemento))
                    ->ignore($this->caixa),
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
            'localidade_criadora_id' => __('Localidade criadora'),
            'numero' => __('Número'),
            'ano' => __('Ano'),
            'guarda_permanente' => __('Guarda Permanente'),
            'complemento' => __('Complemento'),
            'descricao' => __('Descrição'),
        ];
    }
}
