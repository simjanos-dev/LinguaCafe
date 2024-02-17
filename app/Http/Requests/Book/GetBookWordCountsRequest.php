<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class GetBookWordCountsRequest extends FormRequest
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
            'bookId' => 'required|numeric|gte:0'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'bookId' => $this->route('bookId'),
        ]);
    }
}
