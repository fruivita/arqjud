<?php

namespace App\Http\Requests;

use App\Rules\MultiColumnExists;
use App\Rules\NumeroProcesso;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 */
class ShowProcessoHomeRequest extends FormRequest
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
            'termo' => [
                'bail',
                'nullable',
                'string',
                'max:25',
                new NumeroProcesso(),
                new MultiColumnExists('processos', ['numero', 'numero_antigo']),
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
            'termo' => __('Número do processo'),
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
            'termo' => apenasNumeros($this->termo),
        ]);
    }
}
