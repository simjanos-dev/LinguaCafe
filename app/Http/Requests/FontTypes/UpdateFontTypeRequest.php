<?php

namespace App\Http\Requests\FontTypes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFontTypeRequest extends FormRequest
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
            'id' => 'required|numeric|gte:0',
            'name' => 'required|string|min:2|max:128',
            'languages' => 'required|string'
        ];
    }
}
