<?php

namespace App\Http\Requests\Vocabulary;

use Illuminate\Foundation\Http\FormRequest;

class CreatePhraseRequest extends FormRequest
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
            'words' => 'required|json',
            'stage' => 'required|numeric|gte:-7|lte:2',
            'reading' => 'nullable|string',
            'translation' => 'nullable|string'
        ];
    }
}
