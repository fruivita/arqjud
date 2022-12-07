<?php

namespace App\Http\Requests\Cadastro\Andar;

use App\Enums\Policy;
use App\Models\Andar;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class PostAndarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->andar)
            ? auth()->user()->can(Policy::Update->value, Andar::class)  // PATCH OR PUT
            : auth()->user()->can(Policy::Create->value, Andar::class); // POST
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'predio_id' => [
                'bail',
                isset($this->andar)
                    ? 'nullable' // PATCH OR PUT
                    : 'required', // POST
                'integer',
                'exists:predios,id',
            ],

            'numero' => [
                'bail',
                'required',
                'integer',
                'between:-100,300',
                isset($this->andar)
                    ? "unique:andares,numero,{$this->andar->id},id,predio_id,{$this->andar->predio_id}" // PATCH OR PUT
                    : "unique:andares,numero,null,id,predio_id,{$this->predio_id}", // POST
            ],

            'apelido' => [
                'bail',
                'nullable',
                'string',
                'between:1,100',
                isset($this->andar)
                    ? "unique:andares,apelido,{$this->andar->id},id,predio_id,{$this->andar->predio_id}" // PATCH OR PUT
                    : "unique:andares,apelido,null,id,predio_id,{$this->predio_id}", // POST
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
            'predio_id' => __('Prédio'),
            'numero' => __('Número'),
            'apelido' => __('Apelido'),
            'descricao' => __('Descrição'),
        ];
    }
}
