<?php

namespace App\Http\Requests\Dictionaries;

use Illuminate\Foundation\Http\FormRequest;

class GetDictionaryRecordCountRequest extends FormRequest
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
            'dictionaryTableName' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'dictionaryTableName' => $this->route('dictionaryTableName'),
        ]);
    }
}
