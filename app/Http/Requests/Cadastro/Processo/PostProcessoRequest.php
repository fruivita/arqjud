<?php

namespace App\Http\Requests\Cadastro\Processo;

use App\Enums\Policy;
use App\Models\Processo;
use App\Rules\NumeroProcesso;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoAntigoUnique;
use App\Rules\ProcessoExists;
use App\Rules\ProcessoUnique;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostProcessoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->processo)
            ? auth()->user()->can(Policy::Update->value, Processo::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Processo::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'volume_caixa_id' => [
                'bail',
                isset($this->processo)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:volumes_caixa,id',
            ],

            'processo_pai_numero' => [
                'bail',
                'nullable',
                'string',
                'max:25',
                new NumeroProcessoCNJ(),
                'exists:processos,numero',
            ],

            'numero' => [
                'bail',
                'required',
                'string',
                'max:25',
                new NumeroProcessoCNJ(),
                isset($this->processo)
                    ? "unique:processos,numero,{$this->processo->id}" // PATCH OR PUT
                    : "unique:processos,numero", // POST
            ],

            'numero_antigo' => [
                'bail',
                'nullable',
                'string',
                new NumeroProcesso(),
                isset($this->processo)
                    ? "unique:processos,numero_antigo,{$this->processo->id}" // PATCH OR PUT
                    : "unique:processos,numero_antigo", // POST
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
            'volume_caixa_id' => __('Volume da caixa'),
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
        $this->merge([
            'processo_pai_numero' => apenasNumeros($this->processo_pai_numero),
            'numero' => apenasNumeros($this->numero),
            'numero_antigo' => apenasNumeros($this->numero_antigo),
        ]);
    }
}
