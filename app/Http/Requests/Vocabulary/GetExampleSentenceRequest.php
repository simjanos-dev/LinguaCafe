<?php

namespace App\Http\Requests\Vocabulary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetExampleSentenceRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'targetId' => 'required|numeric|gte:0',
            'targetType' => [
                'required',
                'string',
                Rule::in(['word', 'phrase']),
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'targetId' => $this->route('targetId'),
            'targetType' => $this->route('targetType'),
        ]);
    }
}
