<?php

namespace App\Http\Requests\Dictionaries;

use Illuminate\Foundation\Http\FormRequest;

class GetDictionaryRequest extends FormRequest
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
            'dictionaryId' => 'required|numeric',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'dictionaryId' => $this->route('dictionaryId'),
        ]);
    }
}
