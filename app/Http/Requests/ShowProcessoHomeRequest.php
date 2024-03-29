<?php

namespace App\Http\Requests;

use App\Rules\MultiColumnExists;
use App\Rules\NumeroProcesso;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @see https://laravel.com/docs/9.x/validation#form-request-validation
 * @see https://www.aaronsaray.com/2022/be-careful-with-prepareforvalidation
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
                'regex:/^\d+$/',
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
            'termo' => __('Processo'),
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

        if ($this->has('termo')) {
            $merge['termo'] = apenasNumeros($this->get('termo'));
        }

        $this->merge($merge);
    }
}
