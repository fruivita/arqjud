<?php

namespace App\Http\Requests\Api\Solicitacao;

use App\Enums\Policy;
use App\Models\Solicitacao;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoDisponivel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
 */
class ShowProcessoDisponivelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->canany([Policy::ExternoCreate->value, Policy::Create->value], Solicitacao::class);
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
            'numero' => __('Número do processo'),
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $merge = [];

        if ($this->has('numero')) {
            $merge['numero'] = apenasNumeros($this->get('numero'));
        }

        $this->merge($merge);
    }
}
