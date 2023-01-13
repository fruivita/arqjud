<?php

namespace App\Http\Requests\Api\Processo;

use App\Rules\NumeroProcessoCNJ;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
 */
class ShowProcessoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
                'regex:/^\d+$/',
                'max:25',
                new NumeroProcessoCNJ(),
                Rule::exists('processos', 'numero'),
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
            'numero' => __('Processo'),
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
