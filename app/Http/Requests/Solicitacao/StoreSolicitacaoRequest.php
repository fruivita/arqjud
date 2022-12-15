<?php

namespace App\Http\Requests\Solicitacao;

use App\Enums\Policy;
use App\Models\Solicitacao;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoDisponivel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
 */
class StoreSolicitacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::ExternoCreate->value, Solicitacao::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'processos.*.numero' => [
                'bail',
                'required',
                'string',
                'max:25',
                new NumeroProcessoCNJ(),
                Rule::exists('processos', 'numero'),
                new ProcessoDisponivel(),
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
