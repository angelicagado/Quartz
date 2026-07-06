<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'registration_type' => ['required', 'in:static,public'],
            'attendance_type' => ['required', 'in:one-time,am-pm'],
            'evaluation_required' => ['boolean'],
            'certificate_enabled' => ['boolean'],
            'organizer_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
