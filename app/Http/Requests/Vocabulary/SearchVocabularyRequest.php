<?php

namespace App\Http\Requests\Vocabulary;

use Illuminate\Foundation\Http\FormRequest;

class SearchVocabularyRequest extends FormRequest
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
            'text' => 'required|string',
            'book' => 'required|numeric',
            'chapter' => 'required|numeric',
            'stage' => 'required|numeric',
            'phrases' => 'required|string',
            'orderBy' => 'required|string',
            'translation' => 'required|string',
            'page' => 'required|numeric'
        ];
    }
}
