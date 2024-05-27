<?php

namespace App\Http\Requests\Dictionaries;

use Illuminate\Foundation\Http\FormRequest;

class TestDictionaryCsvFileRequest extends FormRequest
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
            'delimiter' => 'nullable|string|max:1',
            'skipHeader' => 'required|string|in:true,false',
        ];
    }
}
