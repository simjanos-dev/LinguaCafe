<?php

namespace App\Http\Requests\Anki;

use Illuminate\Foundation\Http\FormRequest;

class AddCardToAnkiRequest extends FormRequest
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
            'word' => 'required|string',
            'reading' => 'string|nullable',
            'translation' => 'string|nullable',
            'exampleSentence' => 'string'
        ];
    }
}
