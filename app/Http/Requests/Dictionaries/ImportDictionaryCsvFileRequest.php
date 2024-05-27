<?php

namespace App\Http\Requests\Dictionaries;

use Illuminate\Foundation\Http\FormRequest;

class ImportDictionaryCsvFileRequest extends FormRequest
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
            'dictionary' => 'required|file',
            'delimiter' => 'required|string|max:1',
            'skipHeader' => 'required|string|in:true,false',
            'dictionaryName' => 'required|string',
            'databaseName' => 'required|string',
            'sourceLanguage' => 'required|string',
            'targetLanguage' => 'required|string',
            'color' => 'required|string',
        ];
    }
}
