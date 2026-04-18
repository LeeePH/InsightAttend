<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleEmp extends FormRequest
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
            'slug' => 'required|string|min:3|max:32|alpha_dash',
            'schedule_type' => ['required', 'string', Rule::in(['fixed', 'shifting'])],
            'time_in' => 'nullable|date_format:H:i|before:time_out|required_if:schedule_type,fixed',
            'time_out' => 'nullable|date_format:H:i|required_if:schedule_type,fixed',
            'break_minutes' => 'required|integer|min:0|max:600',
            'grace_minutes' => 'required|integer|min:0|max:120',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
        ];
    }
}
