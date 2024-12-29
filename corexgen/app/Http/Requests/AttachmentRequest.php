<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'nullable', // The file can be null if 'files' is provided
                'file',     // Ensures it is a valid file
                'max:' . BULK_CSV_UPLOAD_FILE_SIZE, // Enforce max file size
                'required_without:files', // Required if 'files' is not provided
            ],
            'files.*' => [
                'nullable', // The files array can be null if 'file' is provided
                'file',     // Ensures each item in the array is a valid file
                'max:' . BULK_CSV_UPLOAD_FILE_SIZE, // Enforce max file size for each file
            ],
            'files' => [
                'nullable', // The files array can be null if 'file' is provided
                'array',    // Ensures 'files' is an array
                'required_without:file', // Required if 'file' is not provided
            ],
            'id' => 'required|integer', // ID is always required
        ];
    }


    public function messages(): array
    {
        return [
            'file.required_without' => 'A single file is required if multiple files are not provided.',
            'files.required_without' => 'Multiple files are required if a single file is not provided.',
            'file.max' => 'The single file must not exceed ' . BULK_CSV_UPLOAD_FILE_SIZE . ' kilobytes.',
            'files.*.max' => 'Each file in the files array must not exceed ' . BULK_CSV_UPLOAD_FILE_SIZE . ' kilobytes.',
            'id.required' => 'The ID field is required.',
        ];
    }
}
