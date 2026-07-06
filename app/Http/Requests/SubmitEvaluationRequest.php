<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'responses' => ['required', 'array'],
            'responses.*.question_id' => ['required', 'exists:evaluation_questions,id'],
            'responses.*.response_text' => ['nullable', 'string'],
            'responses.*.response_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}
