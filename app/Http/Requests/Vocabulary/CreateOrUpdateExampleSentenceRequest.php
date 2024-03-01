<?php

namespace App\Http\Requests\Vocabulary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateExampleSentenceRequest extends FormRequest
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
            'exampleSentenceWords' => 'required|json',
        ];
    }
}
