<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class GetReviewItemsRequest extends FormRequest
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
            'practiceMode' => 'required|boolean',
            'chapterId' => 'required|numeric|gte:-1',
            'bookId' => 'required|numeric|gte:-1',
        ];
    }
}
