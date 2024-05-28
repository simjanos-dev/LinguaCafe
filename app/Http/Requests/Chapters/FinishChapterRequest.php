<?php

namespace App\Http\Requests\Chapters;

use Illuminate\Foundation\Http\FormRequest;

class FinishChapterRequest extends FormRequest
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
            'uniqueWords' => 'required|json',
            'autoMoveWordsToKnown' => 'required|boolean',
            'chapterId' => 'required|numeric|gte:0',
        ];
    }
}
