<?php

namespace App\Http\Requests\Movimentacao;

use App\Enums\Policy;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoMovimentavel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
 */
class StoreMoveProcessoEntreCaixaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::MoverProcessoCreate->value);
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

            'processos.*.numero' => [
                'bail',
                'required',
                'string',
                'regex:/^\d+$/',
                'max:25',
                new NumeroProcessoCNJ(),
                Rule::exists('processos', 'numero'),
                new ProcessoMovimentavel(),
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
            'processos.*.numero' => __('Processo'),
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $merge = collect($this->get('processos'))
            ->map(function ($item) {
                return ['numero' => apenasNumeros(Arr::get($item, 'numero'))];
            })->toArray();

        $this->merge([
            'processos' => $merge,
        ]);
    }
}
