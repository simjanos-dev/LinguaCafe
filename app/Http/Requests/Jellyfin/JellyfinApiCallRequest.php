<?php

namespace App\Http\Requests\Jellyfin;

use Illuminate\Foundation\Http\FormRequest;

class JellyfinApiCallRequest extends FormRequest
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
            'method' => 'required|string|in:GET,POST',
            'url' => 'required|string',
        ];
    }
}
