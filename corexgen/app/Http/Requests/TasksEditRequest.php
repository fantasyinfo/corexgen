<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TasksEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return hasPermission('TASKS.UPDATE');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "id" => "required|exists:tasks,id",
            "title" => "required|string|max:200",
            "hourly_rate" => "nullable|numeric|min:0",
            "start_date" => "nullable|date|before_or_equal:due_date",
            "due_date" => "nullable|date|after_or_equal:start_date",
            "priority" => "required|in:Low,Medium,High,Urgent",
            "related_to" => "required",
            "project_id" => "nullable|exists:projects,id",

            "files" => "nullable|array",
            "description" => "nullable|string",
            "assign_to" => "nullable|array",
            "assign_to.*" => "exists:users,id", // Validate each user ID in the array
            "billable" => "nullable|boolean",
            "visible_to_client" => "nullable|boolean",
            'status_id' => 'nullable|exists:category_group_tag,id',
        ];
    }
}
