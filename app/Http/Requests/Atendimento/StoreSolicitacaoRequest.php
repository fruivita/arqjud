<?php

namespace App\Http\Requests\Atendimento;

use App\Enums\Policy;
use App\Models\Solicitacao;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoDisponivel;
use App\Rules\UsuarioHabilitado;
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
        return auth()->user()->can(Policy::Create->value, Solicitacao::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'solicitante_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('usuarios', 'id'),
                new UsuarioHabilitado(),
            ],

            'destino_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists('lotacoes', 'id'),
            ],

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
            'solicitante_id' => __('Solicitante'),
            'destino_id' => __('Destino'),
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
