<?php

namespace App\Http\Requests\Vocabulary;

use Illuminate\Foundation\Http\FormRequest;

class ImportFromCsvRequest extends FormRequest
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
            'importFile' => 'required|file',
            'onlyUpdate' => 'required|boolean',
            'skipHeader' => 'required|boolean',
            'delimiter' => 'required|string|min:1|max:1',
        ];
    }

    protected function prepareForValidation(): void
    {
        /*
            This is required because on the javascript side a FormData object is used,
            and for some reason it messes up the received data type in laravel.
        */
        $this->merge([
            'onlyUpdate' => $this->onlyUpdate === 'true',
            'skipHeader' => $this->skipHeader === 'true',
        ]);
    }
}
