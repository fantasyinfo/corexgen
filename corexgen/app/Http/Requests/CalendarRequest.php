<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (hasPermission('CALENDER.CREATE')) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            // Event Details
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'nullable|string|in:meeting,task,appointment',
            'priority' => 'nullable|string|in:high,medium,low',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'timezone' => 'nullable|string|in:' . implode(',', timezone_identifiers_list()),
            'color' => 'nullable|string|max:7|regex:/^#[0-9a-fA-F]{6}$/', // Validates hex color code


            // Status Management
            'status' => 'nullable|string|in:upcoming,in_progress,completed,canceled,postponed',

            'send_notifications' => 'boolean',
        ];
    }
}
