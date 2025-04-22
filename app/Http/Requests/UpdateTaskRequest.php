<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'name' => 'sometimes|max:50',
            'description' => 'sometimes|max:300',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'deadline' => 'nullable|date',
            'assign_to' => 'nullable|exists:users,id',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048'
        ];
    }
}
