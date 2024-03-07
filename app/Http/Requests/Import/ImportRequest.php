<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
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
            'importType' => 'required|string',
            'textProcessingMethod' => 'required|string',
            'bookId' => 'required|numeric|gte:-1',
            'bookName' => 'nullable|string',
            'chapterName' => 'required|string',
            'maximumCharactersPerChapter' => 'required|numeric|gte:200|lte:20000',
        ];
    }
}
