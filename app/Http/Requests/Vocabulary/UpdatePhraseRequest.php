<?php

namespace App\Http\Requests\Vocabulary;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhraseRequest extends FormRequest
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
            'id' => 'required|numeric|gte:0',
            'stage' => 'numeric|gte:-7|lte:0',
            'translation' => 'nullable|string',
            'reading' => 'nullable|string',
            'lookup_count' => 'nullable|numeric|gte:0',
            'relearning' => 'nullable|boolean',
        ];
    }
}
