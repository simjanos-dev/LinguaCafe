<?php

namespace App\Http\Requests\Chapters;

use Illuminate\Foundation\Http\FormRequest;

class RetryFailedChaptersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bookId' => 'required|numeric|gte:0',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'bookId' => $this->route('bookId'),
        ]);
    }
}
