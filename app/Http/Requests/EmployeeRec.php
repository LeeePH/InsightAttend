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
            'name' => ['required', 'string', 'min:3', 'max:64'],
            'position' => ['required', 'string', 'min:2', 'max:64'],
            'department_id' => ['required', 'exists:departments,id'],
            'phone' => ['nullable', 'regex:/^\+63\d{10}$/'],
            'email' => [
                'nullable',
                'email',
                Rule::requiredIf(function () {
                    return strlen((string) $this->input('password', '')) > 0;
                }),
            ],
            'date_hired' => ['nullable', 'date'],
            'employment_type' => ['nullable', 'in:full_time,part_time'],
            'skills' => ['nullable', 'string', 'max:5000'],
            'achievements' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'schedule' => $this->routeIs('employees.store')
                ? ['nullable', 'exists:schedules,slug']
                : ['required', 'exists:schedules,slug'],
            'portal_role' => ['required', 'in:employee,secretary'],
            'schedule_department_key' => [
                'nullable',
                'string',
                'in:IT,EDUC,SHTM',
                Rule::requiredIf(fn () => $this->input('portal_role') === 'secretary'),
            ],
            'rotation_start_date' => ['nullable', 'date'],
            'rotation_pattern' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'face_descriptor' => 'nullable|json',
            'face_image' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required when a login password is set.',
            'password.min' => 'Password must be at least 8 characters.',
            'phone.regex' => 'Phone number must be in +63 format (e.g. +639xxxxxxxxx).',
            'schedule_department_key.required' => 'Choose which department (IT, EDUC, or SHTM) this secretary will manage.',
        ];
    }
}
