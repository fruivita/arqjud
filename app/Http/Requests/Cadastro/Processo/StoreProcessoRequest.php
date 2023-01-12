<?php

namespace App\Http\Requests\Cadastro\Processo;

use App\Enums\Policy;
use App\Models\Processo;
use App\Rules\NumeroProcesso;
use App\Rules\NumeroProcessoCNJ;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
 */
class StoreProcessoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(Policy::Create->value, Processo::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'processo_pai_numero' => [
                'bail',
                'nullable',
                'string',
                'regex:/\d+/',
                'max:25',
                new NumeroProcessoCNJ(),
                Rule::exists('processos', 'numero'),
            ],

            'numero' => [
                'bail',
                'required',
                'string',
                'regex:/\d+/',
                'max:25',
                new NumeroProcessoCNJ(),
                Rule::unique('processos', 'numero'),
            ],

            'numero_antigo' => [
                'bail',
                'nullable',
                'string',
                new NumeroProcesso(),
                Rule::unique('processos', 'numero_antigo'),
            ],

            'arquivado_em' => [
                'bail',
                'required',
                'date_format:d-m-Y',
                'after_or_equal:01-01-1900',
                'before_or_equal:' . now()->format('d-m-Y'),
            ],

            'qtd_volumes' => [
                'bail',
                'required',
                'integer',
                'between:1,9999',
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
            'processo_pai_numero' => __('Processo pai'),
            'numero' => __('Número do processo'),
            'numero_antigo' => __('Número antigo do processo'),
            'arquivado_em' => __('Data de arquivamento'),
            'qtd_volumes' => __('Qtd volumes'),
            'descricao' => __('Descrição'),
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

        if ($this->has('processo_pai_numero')) {
            $merge['processo_pai_numero'] = apenasNumeros($this->get('processo_pai_numero'));
        }
        if ($this->has('numero')) {
            $merge['numero'] = apenasNumeros($this->get('numero'));
        }
        if ($this->has('numero_antigo')) {
            $merge['numero_antigo'] = apenasNumeros($this->get('numero_antigo'));
        }

        $this->merge($merge);
    }
}
