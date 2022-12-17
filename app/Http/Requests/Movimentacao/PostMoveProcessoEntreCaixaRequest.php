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
 */
class PostMoveProcessoEntreCaixaRequest extends FormRequest
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
            'volume_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('volumes_caixa', 'id'),
            ],

            'processos.*.numero' => [
                'bail',
                'required',
                'string',
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
            'volume_id' => __('Volume de destino'),
            'processos.*.numero' => __('Número do processo'),
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
