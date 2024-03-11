<?php

namespace App\Http\Requests\Image;

use Illuminate\Foundation\Http\FormRequest;

class GetBookImageRequest extends FormRequest
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
            'fileName' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'fileName' => $this->route('fileName'),
        ]);
    }
}
