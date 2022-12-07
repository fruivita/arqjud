<?php

namespace App\Http\Requests\Cadastro\Caixa;

use App\Enums\Policy;
use App\Models\Caixa;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostCaixaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->caixa)
            ? auth()->user()->can(Policy::Update->value, Caixa::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Caixa::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'prateleira_id' => [
                'bail',
                isset($this->caixa)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:prateleiras,id',
            ],

            'localidade_criadora_id' => [
                'bail',
                'required',
                'integer',
                'exists:localidades,id',
            ],

            'numero' => [
                'bail',
                'required',
                'integer',
                'min:1',
                isset($this->caixa)
                    ? "unique:caixas,numero,{$this->caixa->id},id,ano,{$this->caixa->ano},guarda_permanente,{$this->caixa->guarda_permanente},complemento,{$this->caixa->complemento},prateleira_id,{$this->caixa->prateleira_id},localidade_criadora_id,{$this->caixa->localidade_criadora_id}" // PATCH OR PUT
                    : "unique:caixas,numero,null,id,ano,{$this->ano},guarda_permanente,{$this->guarda_permanente},complemento,{$this->complemento},prateleira_id,{$this->prateleira_id},localidade_criadora_id,{$this->localidade_criadora_id}", // POST
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
            'prateleira_id' => __('Prateleira'),
            'localidade_criadora_id' => __('Localidade criadora'),
            'numero' => __('Número'),
            'ano' => __('Ano'),
            'guarda_permanente' => __('Guarda Permanente'),
            'complemento' => __('Complemento'),
            'descricao' => __('Descrição'),
        ];
    }
}
