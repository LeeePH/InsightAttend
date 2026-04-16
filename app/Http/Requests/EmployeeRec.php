<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRec extends FormRequest
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
            'name' => ['required', 'string', 'min:3', 'max:64', 'regex:/^[A-Za-z][A-Za-z\s\.\'-]*$/'],
            'position' => ['required', 'string', 'min:2', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9\s\.\-\/&]*$/'],
            'department' => 'nullable|string|max:64',
            'email' => [
                'nullable',
                'email',
                Rule::requiredIf(function () {
                    return strlen((string) $this->input('password', '')) > 0;
                }),
            ],
            'schedule' => 'required|exists:schedules,slug',
            'password' => ['nullable', 'string', 'min:8'],
            'face_descriptor' => 'nullable|json',
            'face_image' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'Name may contain letters, spaces, apostrophes, dots, and hyphens only.',
            'position.regex' => 'Position may contain letters, numbers, spaces, dots, hyphens, slashes, and ampersands only.',
            'email.required' => 'Email is required when a login password is set.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}
