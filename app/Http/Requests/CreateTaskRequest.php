<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use function App\apiResponse;

class CreateTaskRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name' => 'required|max:50',
            'description' => 'required|max:300',
            'status' => 'required|in:pending,in_progress,completed',
            'deadline' => 'nullable|date',
            'assign_to' => 'nullable|exists:users,id',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048'
        ];
    }


    public function messages()
    {
        return [
            'status.in' => 'Status must be one of: pending, in_progress, or completed',
            'assign_to.exists' => 'The selected user to assign does not exist',
            'image.mimes' => 'Image must be one of the following types: jpg, jpeg, png',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            apiResponse(422, $validator->errors(), 'Validation Failed')
        );
    }
}
