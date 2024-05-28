<?php

namespace App\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarDataRequest extends FormRequest
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
            'achievementGoalId' => 'required|numeric|gte:-1',
            'achievementType' => 'required|string',
            'day' => 'required|string',
            'newValue' => 'required',
        ];
    }
}
