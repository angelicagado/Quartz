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
            'registration_start_date' => ['nullable', 'date'],
            'registration_end_date' => ['nullable', 'date', 'after_or_equal:registration_start_date'],
            'registration_type' => ['required', 'in:open,scheduled,approval,closed'],
            'sessions' => ['required', 'array', 'min:1'],
            'sessions.*.id' => ['nullable', 'exists:event_sessions,id'],
            'sessions.*.name' => ['required', 'string', 'max:255'],
            'sessions.*.start_time' => ['required', 'date'],
            'sessions.*.end_time' => ['required', 'date', 'after:sessions.*.start_time'],
            'sessions.*.requires_checkout' => ['boolean'],
            'evaluation_required' => ['boolean'],
            'certificate_enabled' => ['boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'organizer_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
