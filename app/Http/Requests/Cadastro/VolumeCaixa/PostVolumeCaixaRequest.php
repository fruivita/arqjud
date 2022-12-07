<?php

namespace App\Http\Requests\Cadastro\VolumeCaixa;

use App\Enums\Policy;
use App\Models\VolumeCaixa;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostVolumeCaixaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->volume_caixa)
            ? auth()->user()->can(Policy::Update->value, VolumeCaixa::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, VolumeCaixa::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'caixa_id' => [
                'bail',
                isset($this->volume_caixa)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:caixas,id',
            ],

            'numero' => [
                'bail',
                'required',
                'integer',
                'between:1,9999',
                isset($this->volume_caixa)
                    ? "unique:volumes_caixa,numero,{$this->volume_caixa->id},id,caixa_id,{$this->volume_caixa->caixa_id}" // PATCH OR PUT
                    : "unique:volumes_caixa,numero,null,id,caixa_id,{$this->caixa_id}", // POST
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
            'caixa_id' => __('Caixa'),
            'numero' => __('Número'),
            'descricao' => __('Descrição'),
        ];
    }
}
