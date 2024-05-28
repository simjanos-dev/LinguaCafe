<?php

namespace App\Http\Requests\Chapters;

use Illuminate\Foundation\Http\FormRequest;

class CreateChapterRequest extends FormRequest
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
            'chapterName' => 'required|string|max:128',
            'chapterText' => 'string|nullable',
            'bookId' => 'required|numeric|gte:0',
        ];
    }
}
