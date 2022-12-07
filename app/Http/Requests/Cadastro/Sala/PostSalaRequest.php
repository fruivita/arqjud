<?php

namespace App\Http\Requests\Cadastro\Sala;

use App\Enums\Policy;
use App\Models\Sala;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostSalaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->sala)
            ? auth()->user()->can(Policy::Update->value, Sala::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Sala::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'andar_id' => [
                'bail',
                isset($this->sala)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:andares,id',
            ],

            'numero' => [
                'bail',
                'required',
                'string',
                'between:1,50',
                isset($this->sala)
                    ? "unique:salas,numero,{$this->sala->id},id,andar_id,{$this->sala->andar_id}" // PATCH OR PUT
                    : "unique:salas,numero,null,id,andar_id,{$this->andar_id}", // POST
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
            'andar_id' => __('Andar'),
            'numero' => __('Número'),
            'descricao' => __('Descrição'),
        ];
    }
}
