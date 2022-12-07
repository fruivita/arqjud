<?php

namespace App\Http\Requests\Cadastro\Estante;

use App\Enums\Policy;
use App\Models\Estante;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostEstanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->estante)
            ? auth()->user()->can(Policy::Update->value, Estante::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Estante::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sala_id' => [
                'bail',
                isset($this->estante)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:salas,id',
            ],

            'numero' => [
                'bail',
                'required',
                'string',
                'between:1,50',
                isset($this->estante)
                    ? "unique:estantes,numero,{$this->estante->id},id,sala_id,{$this->estante->sala_id}" // PATCH OR PUT
                    : "unique:estantes,numero,null,id,sala_id,{$this->sala_id}", // POST
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
            'sala_id' => __('Sala'),
            'numero' => __('Número'),
            'descricao' => __('Descrição'),
        ];
    }
}
