<?php

namespace App\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
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
            'goalId' => 'required|numeric|gte:0',
            'newGoalQuantity' => 'required|numeric|gte:0',
        ];
    }
}
