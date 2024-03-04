<?php

namespace App\Http\Requests\Home;

use Illuminate\Foundation\Http\FormRequest;

class GetConfigRequest extends FormRequest
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
            'configPath' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'configPath' => $this->route('configPath'),
        ]);
    }
}
