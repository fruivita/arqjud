<?php

namespace App\Http\Requests\Cadastro\Prateleira;

use App\Enums\Policy;
use App\Models\Prateleira;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostPrateleiraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->prateleira)
            ? auth()->user()->can(Policy::Update->value, Prateleira::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Prateleira::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'estante_id' => [
                'bail',
                isset($this->prateleira)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:estantes,id',
            ],

            'numero' => [
                'bail',
                'required',
                'string',
                'between:1,50',
                isset($this->prateleira)
                    ? "unique:prateleiras,numero,{$this->prateleira->id},id,estante_id,{$this->prateleira->estante_id}" // PATCH OR PUT
                    : "unique:prateleiras,numero,null,id,estante_id,{$this->estante_id}", // POST
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
            'estante_id' => __('Estante'),
            'numero' => __('Número'),
            'descricao' => __('Descrição'),
        ];
    }
}
