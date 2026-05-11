<?php

namespace App\Services;

use Illuminate\Support\Collection;

class EmployeeRequestFormService
{
    public function ensureDefaults(): void
    {
        // Legacy maintenance-form templates were removed.
        // Request forms now use the built-in defaults in this service.
    }

    public function getTemplate(string $slug)
    {
        return null;
    }

    public function getManagedTemplates()
    {
        return new Collection();
    }

    public function applyTemplateRules(string $slug, array $defaults): array
    {
        $template = $this->getTemplate($slug);
        if (!$template) {
            return $defaults;
        }

        $rules = $defaults;
        foreach ($template->fields as $field) {
            $key = $field->field_key;
            if (!array_key_exists($key, $rules)) {
                continue;
            }

            if (!empty($field->validation_rules)) {
                $rules[$key] = $field->validation_rules;
                continue;
            }

            $rules[$key] = $this->applyRequiredFlag($rules[$key], (bool) $field->is_required);
        }

        return $rules;
    }

    public function fieldLabel($template, string $fieldKey, string $fallback): string
    {
        if (!$template) {
            return $fallback;
        }

        $field = $template->fields->firstWhere('field_key', $fieldKey);
        return $field && $field->label ? $field->label : $fallback;
    }

    public function fieldRequired($template, string $fieldKey, bool $fallback = false): bool
    {
        if (!$template) {
            return $fallback;
        }

        $field = $template->fields->firstWhere('field_key', $fieldKey);
        if (!$field) {
            return $fallback;
        }

        return (bool) $field->is_required;
    }

    public function selectOptions($template, string $fieldKey, array $fallback): array
    {
        if (!$template) {
            return $fallback;
        }

        $field = $template->fields->firstWhere('field_key', $fieldKey);
        if (!$field || !$field->field_options) {
            return $fallback;
        }

        $result = [];
        $pairs = explode(',', $field->field_options);
        foreach ($pairs as $pair) {
            $item = trim($pair);
            if ($item === '') {
                continue;
            }

            $parts = explode(':', $item, 2);
            if (count($parts) === 2) {
                $result[trim($parts[0])] = trim($parts[1]);
            } else {
                $result[trim($parts[0])] = trim($parts[0]);
            }
        }

        return count($result) > 0 ? $result : $fallback;
    }

    /**
     * Merged theme + copy for employee-facing pages (leave / resignation).
     */
    public function getMergedUiSettings($template, string $slug): array
    {
        $defaults = $this->defaultUiSettingsForSlug($slug);
        $saved = ($template && is_array($template->ui_settings)) ? $template->ui_settings : [];

        return array_merge($defaults, is_array($saved) ? $saved : []);
    }

    /**
     * Per-field presentation (placeholder, help, column width) for employee forms.
     */
    public function fieldUiMap($template, string $slug): array
    {
        $defaults = $this->defaultFieldUiBySlug($slug);
        if (!$template) {
            return $defaults;
        }

        foreach ($template->fields as $field) {
            $key = $field->field_key;
            if (!isset($defaults[$key])) {
                continue;
            }
            if ($field->placeholder !== null && $field->placeholder !== '') {
                $defaults[$key]['placeholder'] = $field->placeholder;
            }
            if ($field->help_text !== null && $field->help_text !== '') {
                $defaults[$key]['help_text'] = $field->help_text;
            }
            if ($field->column_class !== null && $field->column_class !== '') {
                $defaults[$key]['column_class'] = $this->sanitizeColumnClass($field->column_class);
            }
        }

        return $defaults;
    }

    /**
     * Apply admin-submitted UI keys onto defaults (only keys present in $raw are updated).
     *
     * @param  array<string, mixed>  $raw
     */
    public function mergeUiSettingsFromInput(array $raw, string $slug): array
    {
        $defaults = $this->defaultUiSettingsForSlug($slug);

        foreach ($raw as $key => $val) {
            if (!array_key_exists($key, $defaults)) {
                continue;
            }

            if (in_array($key, ['show_employee_panel', 'show_days_banner', 'show_notice_box'], true)) {
                $defaults[$key] = filter_var($val, FILTER_VALIDATE_BOOLEAN);

                continue;
            }

            if (in_array($key, ['accent', 'accent_soft', 'bg', 'text', 'muted', 'border'], true)) {
                if (is_string($val) && preg_match('/^#[0-9A-Fa-f]{6}$/', $val)) {
                    $defaults[$key] = $val;
                }

                continue;
            }

            if ($key === 'card_radius') {
                if ($val === null || $val === '') {
                    continue;
                }
                $n = (int) $val;
                if ($n >= 0 && $n <= 40) {
                    $defaults[$key] = (string) $n;
                }

                continue;
            }

            if ($key === 'reason_footer_note' || $key === 'notice_text') {
                if (is_string($val)) {
                    $defaults[$key] = mb_substr($val, 0, 2000);
                }

                continue;
            }

            if (is_string($val)) {
                $defaults[$key] = mb_substr($val, 0, 120);
            }
        }

        return $defaults;
    }

    public function sanitizeColumnClass(?string $class): string
    {
        $allowed = ['col-12', 'col-md-12', 'col-md-8', 'col-md-6', 'col-md-4', 'col-lg-8', 'col-lg-6'];
        $class = trim((string) $class);

        return in_array($class, $allowed, true) ? $class : 'col-md-6';
    }

    private function defaultUiSettingsForSlug(string $slug): array
    {
        $base = [
            'accent' => '#8B4513',
            'accent_soft' => '#f3e4d7',
            'bg' => '#f8f1eb',
            'text' => '#3e2412',
            'muted' => '#7b5a45',
            'border' => '#e2cdbd',
            'card_radius' => '14',
            'submit_label' => 'Submit request',
            'back_label_employee' => 'Back to Dashboard',
            'back_label_guest' => 'Back to Home',
            'employee_panel_title' => 'Employee Information',
            'show_employee_panel' => true,
            'show_days_banner' => false,
            'reason_footer_note' => '',
            'show_notice_box' => false,
            'notice_text' => '',
        ];

        if ($slug === 'leave_request') {
            $base['submit_label'] = 'Submit Request';
            $base['show_days_banner'] = true;
            $base['reason_footer_note'] = 'Note: Sick Leave over 3 days requires the submission of a medical certificate from a bona fide Physician.';
        }

        if ($slug === 'resignation_request') {
            $base['submit_label'] = 'Submit Resignation Request';
            $base['show_notice_box'] = true;
            $base['notice_text'] = 'Please provide accurate handover details to support a smooth transition process.';
        }

        if ($slug === 'loan_request') {
            $base['submit_label'] = 'Submit Loan Application';
            $base['show_notice_box'] = true;
            $base['notice_text'] = 'Please attach any supporting documents required for your selected loan purpose.';
        }

        if ($slug === 'discount_request') {
            $base['submit_label'] = 'Submit Application for Discount';
            $base['show_notice_box'] = true;
            $base['notice_text'] = 'Please make sure all student and employee details are accurate before submitting.';
        }

        if ($slug === 'overtime_authorization_request') {
            $base['submit_label'] = 'Submit Overtime Authorization';
            $base['show_notice_box'] = true;
            $base['notice_text'] = 'Please provide complete schedule and overtime details for each row.';
        }

        if ($slug === 'undertime_authorization_request') {
            $base['submit_label'] = 'Submit Undertime Authorization';
            $base['show_notice_box'] = true;
            $base['notice_text'] = 'Please provide complete schedule and undertime details for each row.';
        }

        if ($slug === 'permit_to_teach_outside_request') {
            $base['submit_label'] = 'Submit Permit to Teach Form';
            $base['show_notice_box'] = true;
            $base['notice_text'] = 'Ensure outside teaching details do not conflict with your official work schedule.';
        }

        if ($slug === 'substitution_form_request') {
            $base['submit_label'] = 'Submit Subsitution Form';
            $base['show_notice_box'] = true;
            $base['notice_text'] = 'Provide complete class schedule details for substitution review.';
        }

        return $base;
    }

    private function defaultFieldUiBySlug(string $slug): array
    {
        if ($slug === 'leave_request') {
            return [
                'leave_date' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'leave_date_end' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'type' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
                'other_type' => [
                    'placeholder' => 'Please specify',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
                'reason' => [
                    'placeholder' => 'Enter reason for leave',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
            ];
        }

        if ($slug === 'resignation_request') {
            return [
                'last_working_day' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'reason' => [
                    'placeholder' => 'Enter your reason for resignation',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
                'handover_notes' => [
                    'placeholder' => 'Include key tasks, files, and pending responsibilities',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
                'acknowledgement' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
            ];
        }

        if ($slug === 'loan_request') {
            return [
                'date_filed' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'civil_status' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'contact_number' => [
                    'placeholder' => 'Enter contact number',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'hire_date' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'amount_requested' => [
                    'placeholder' => '0.00',
                    'help_text' => '',
                    'column_class' => 'col-md-6',
                ],
                'purpose' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
                'other_purpose' => [
                    'placeholder' => 'Please specify',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
                'employee_statement' => [
                    'placeholder' => '',
                    'help_text' => '',
                    'column_class' => 'col-12',
                ],
            ];
        }

        if ($slug === 'discount_request') {
            return [
                'term_semester' => ['placeholder' => 'e.g. 1st Semester', 'help_text' => '', 'column_class' => 'col-md-6'],
                'school_year' => ['placeholder' => 'e.g. 2026-2027', 'help_text' => '', 'column_class' => 'col-md-6'],
                'date_request' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_department' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_position' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'date_hire' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employment_status' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-12'],
                'school' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-12'],
                'student_name' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'student_no' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'track_program' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'grade_year_level' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'student_department' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-12'],
                'discount_applied' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-12'],
                'family_relationship' => ['placeholder' => 'Relationship', 'help_text' => '', 'column_class' => 'col-md-6'],
                'privilege_child_order' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
            ];
        }

        if ($slug === 'overtime_authorization_request') {
            return [
                'date_filed' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_name' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_position' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_department' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
            ];
        }

        if ($slug === 'undertime_authorization_request') {
            return [
                'date_filed' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_name' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_position' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employee_department' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
            ];
        }

        if ($slug === 'permit_to_teach_outside_request') {
            return [
                'position_rank' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'department_school' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'employment_status' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-12'],
                'other_school_name' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'other_school_address' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'institution_type' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-12'],
                'institution_type_others' => ['placeholder' => 'Please specify', 'help_text' => '', 'column_class' => 'col-md-6'],
                'subjects_to_teach' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'program_level' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-12'],
                'units_or_hours_per_week' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'teaching_schedule' => ['placeholder' => 'Day / Time / Subject', 'help_text' => '', 'column_class' => 'col-12'],
                'engagement_from' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'engagement_to' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
            ];
        }

        if ($slug === 'substitution_form_request') {
            return [
                'absent_teacher_name' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
                'substitute_teacher_name' => ['placeholder' => '', 'help_text' => '', 'column_class' => 'col-md-6'],
            ];
        }

        return [];
    }

    private function applyRequiredFlag(string $ruleString, bool $isRequired): string
    {
        $parts = explode('|', $ruleString);
        $filtered = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === 'required' || $part === 'nullable') {
                continue;
            }
            if ($part !== '') {
                $filtered[] = $part;
            }
        }

        array_unshift($filtered, $isRequired ? 'required' : 'nullable');

        return implode('|', $filtered);
    }

    private function defaultDefinitions(): array
    {
        return [
            'leave_request' => [
                'name' => 'Request Leave',
                'description' => 'Employee leave request form',
                'fields' => [
                    ['label' => 'Start Date', 'field_key' => 'leave_date', 'field_type' => 'date', 'validation_rules' => 'required|date', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'End Date', 'field_key' => 'leave_date_end', 'field_type' => 'date', 'validation_rules' => 'required|date|after_or_equal:leave_date', 'is_required' => true, 'sort_order' => 2],
                    ['label' => 'Leave Type', 'field_key' => 'type', 'field_type' => 'select', 'field_options' => '1:Sick Leave,2:Annual Leave,3:Personal Leave,4:Maternity Leave,5:Paternity Leave,6:Vacation Leave,7:Emergency Leave,8:Other', 'validation_rules' => 'required|integer|between:1,8', 'is_required' => true, 'sort_order' => 3],
                    ['label' => 'Other Leave Type', 'field_key' => 'other_type', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:255', 'is_required' => false, 'sort_order' => 4],
                    ['label' => 'Reason', 'field_key' => 'reason', 'field_type' => 'textarea', 'validation_rules' => 'required|string|max:500', 'is_required' => true, 'sort_order' => 5],
                ],
            ],
            'resignation_request' => [
                'name' => 'Resignation Request',
                'description' => 'Employee resignation request form',
                'fields' => [
                    ['label' => 'Last Working Day', 'field_key' => 'last_working_day', 'field_type' => 'date', 'validation_rules' => 'required|date|after_or_equal:today', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'Reason for Resignation', 'field_key' => 'reason', 'field_type' => 'textarea', 'validation_rules' => 'required|string|max:2000', 'is_required' => true, 'sort_order' => 2],
                    ['label' => 'Handover Notes', 'field_key' => 'handover_notes', 'field_type' => 'textarea', 'validation_rules' => 'nullable|string|max:2000', 'is_required' => false, 'sort_order' => 3],
                    ['label' => 'Acknowledgement', 'field_key' => 'acknowledgement', 'field_type' => 'checkbox', 'validation_rules' => 'required|accepted', 'is_required' => true, 'sort_order' => 4],
                ],
            ],
            'loan_request' => [
                'name' => 'Company Loan Application',
                'description' => 'Employee company loan application form',
                'fields' => [
                    ['label' => 'Date Filed', 'field_key' => 'date_filed', 'field_type' => 'date', 'validation_rules' => 'required|date', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'Civil Status', 'field_key' => 'civil_status', 'field_type' => 'select', 'field_options' => 'single:Single,married:Married', 'validation_rules' => 'required|string|in:single,married', 'is_required' => true, 'sort_order' => 2],
                    ['label' => 'Contact Number', 'field_key' => 'contact_number', 'field_type' => 'text', 'validation_rules' => 'required|string|max:32', 'is_required' => true, 'sort_order' => 3],
                    ['label' => 'Hire Date', 'field_key' => 'hire_date', 'field_type' => 'date', 'validation_rules' => 'required|date', 'is_required' => true, 'sort_order' => 4],
                    ['label' => 'Amount Requested', 'field_key' => 'amount_requested', 'field_type' => 'text', 'validation_rules' => 'required|numeric|min:0.01|max:999999.99', 'is_required' => true, 'sort_order' => 5],
                    ['label' => 'Loan Purpose', 'field_key' => 'purpose', 'field_type' => 'checkbox_group', 'validation_rules' => 'required|array|min:1', 'is_required' => true, 'sort_order' => 6],
                    ['label' => 'Other Purpose', 'field_key' => 'other_purpose', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:255', 'is_required' => false, 'sort_order' => 7],
                    ['label' => 'Employee Statement', 'field_key' => 'employee_statement', 'field_type' => 'checkbox', 'validation_rules' => 'required|accepted', 'is_required' => true, 'sort_order' => 8],
                ],
            ],
            'discount_request' => [
                'name' => 'Application for Discount Form',
                'description' => 'Employee application for discount',
                'fields' => [
                    ['label' => 'Term/Semester', 'field_key' => 'term_semester', 'field_type' => 'text', 'validation_rules' => 'required|string|max:64', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'School/Academic Year', 'field_key' => 'school_year', 'field_type' => 'text', 'validation_rules' => 'required|string|max:64', 'is_required' => true, 'sort_order' => 2],
                    ['label' => 'Date of Request', 'field_key' => 'date_request', 'field_type' => 'date', 'validation_rules' => 'required|date', 'is_required' => true, 'sort_order' => 3],
                    ['label' => 'Department', 'field_key' => 'employee_department', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 4],
                    ['label' => 'Position', 'field_key' => 'employee_position', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 5],
                    ['label' => 'Date of Hire', 'field_key' => 'date_hire', 'field_type' => 'date', 'validation_rules' => 'required|date', 'is_required' => true, 'sort_order' => 6],
                    ['label' => 'Employment Status', 'field_key' => 'employment_status', 'field_type' => 'select', 'field_options' => 'probationary:Probationary,regular:Regular', 'validation_rules' => 'required|in:probationary,regular', 'is_required' => true, 'sort_order' => 7],
                    ['label' => 'School', 'field_key' => 'school', 'field_type' => 'select', 'field_options' => 'stsn:STSN,csta:CSTA', 'validation_rules' => 'required|in:stsn,csta', 'is_required' => true, 'sort_order' => 8],
                    ['label' => 'Name of Student', 'field_key' => 'student_name', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 9],
                    ['label' => 'Student No', 'field_key' => 'student_no', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 10],
                    ['label' => 'Track & Strand/Program', 'field_key' => 'track_program', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 11],
                    ['label' => 'Grade/Year Level', 'field_key' => 'grade_year_level', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 12],
                    ['label' => 'Student Department', 'field_key' => 'student_department', 'field_type' => 'select', 'field_options' => 'grade_school:Grade School,junior_high:Junior High School,senior_high:Senior High School,college:College', 'validation_rules' => 'required|in:grade_school,junior_high,senior_high,college', 'is_required' => true, 'sort_order' => 13],
                    ['label' => 'Discount Applied', 'field_key' => 'discount_applied', 'field_type' => 'select', 'field_options' => 'family_relative:Family Relative (sponsorship),employee_privileges:Employee Privileges', 'validation_rules' => 'required|in:family_relative,employee_privileges', 'is_required' => true, 'sort_order' => 14],
                    ['label' => 'Relationship', 'field_key' => 'family_relationship', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:255', 'is_required' => false, 'sort_order' => 15],
                    ['label' => 'Employee Privileges Child', 'field_key' => 'privilege_child_order', 'field_type' => 'select', 'field_options' => '1st:1st child,2nd:2nd child,3rd:3rd child,4th:4th child', 'validation_rules' => 'nullable|in:1st,2nd,3rd,4th', 'is_required' => false, 'sort_order' => 16],
                ],
            ],
            'overtime_authorization_request' => [
                'name' => 'Overtime Authorization Form',
                'description' => 'Employee overtime authorization request form',
                'fields' => [
                    ['label' => 'Date Filed', 'field_key' => 'date_filed', 'field_type' => 'date', 'validation_rules' => 'required|date', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'Employee Name', 'field_key' => 'employee_name', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 2],
                    ['label' => 'Position', 'field_key' => 'employee_position', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 3],
                    ['label' => 'Department', 'field_key' => 'employee_department', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 4],
                ],
            ],
            'undertime_authorization_request' => [
                'name' => 'Undertime Authorization Form',
                'description' => 'Employee undertime authorization request form',
                'fields' => [
                    ['label' => 'Date Filed', 'field_key' => 'date_filed', 'field_type' => 'date', 'validation_rules' => 'required|date', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'Employee Name', 'field_key' => 'employee_name', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 2],
                    ['label' => 'Position', 'field_key' => 'employee_position', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 3],
                    ['label' => 'Department', 'field_key' => 'employee_department', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 4],
                ],
            ],
            'permit_to_teach_outside_request' => [
                'name' => 'Permit to Teach (Outside School) Application Form',
                'description' => 'Employee permit request for outside teaching engagement',
                'fields' => [
                    ['label' => 'Position / Rank', 'field_key' => 'position_rank', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:255', 'is_required' => false, 'sort_order' => 1],
                    ['label' => 'Department / School', 'field_key' => 'department_school', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:255', 'is_required' => false, 'sort_order' => 2],
                    ['label' => 'Employment Status', 'field_key' => 'employment_status', 'field_type' => 'select', 'field_options' => 'full_time:Full-time,part_time:Part-time,probationary:Probationary,regular:Regular', 'validation_rules' => 'required|in:full_time,part_time,probationary,regular', 'is_required' => true, 'sort_order' => 3],
                    ['label' => 'Name of Other School/Institution', 'field_key' => 'other_school_name', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 4],
                    ['label' => 'School Address', 'field_key' => 'other_school_address', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 5],
                    ['label' => 'Type of Institution', 'field_key' => 'institution_type', 'field_type' => 'select', 'field_options' => 'public:Public,private:Private,review_center:Review Center,others:Others', 'validation_rules' => 'required|in:public,private,review_center,others', 'is_required' => true, 'sort_order' => 6],
                    ['label' => 'Others (Institution Type)', 'field_key' => 'institution_type_others', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:255', 'is_required' => false, 'sort_order' => 7],
                    ['label' => 'Subject(s) to be Taught', 'field_key' => 'subjects_to_teach', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 8],
                    ['label' => 'Program / Level', 'field_key' => 'program_level', 'field_type' => 'select', 'field_options' => 'basic_ed:Basic Ed,senior_high:Senior High,college:College,graduate:Graduate', 'validation_rules' => 'required|in:basic_ed,senior_high,college,graduate', 'is_required' => true, 'sort_order' => 9],
                    ['label' => 'No. of Units / Hours per Week', 'field_key' => 'units_or_hours_per_week', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:100', 'is_required' => false, 'sort_order' => 10],
                    ['label' => 'Teaching Schedule in Other School', 'field_key' => 'teaching_schedule', 'field_type' => 'textarea', 'validation_rules' => 'nullable|string|max:2000', 'is_required' => false, 'sort_order' => 11],
                    ['label' => 'Duration From', 'field_key' => 'engagement_from', 'field_type' => 'date', 'validation_rules' => 'nullable|date', 'is_required' => false, 'sort_order' => 12],
                    ['label' => 'Duration To', 'field_key' => 'engagement_to', 'field_type' => 'date', 'validation_rules' => 'nullable|date|after_or_equal:engagement_from', 'is_required' => false, 'sort_order' => 13],
                    ['label' => 'Certification Confirmed', 'field_key' => 'certification_confirmed', 'field_type' => 'checkbox', 'validation_rules' => 'required|accepted', 'is_required' => true, 'sort_order' => 14],
                ],
            ],
            'substitution_form_request' => [
                'name' => 'Subsitution Form',
                'description' => 'Employee class subsitution request form',
                'fields' => [
                    ['label' => 'Name of Absent Teacher', 'field_key' => 'absent_teacher_name', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'Name of Substitute Teacher', 'field_key' => 'substitute_teacher_name', 'field_type' => 'text', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 2],
                    ['label' => 'Schedule Entries', 'field_key' => 'entries', 'field_type' => 'repeater', 'validation_rules' => 'required|array|min:1', 'is_required' => true, 'sort_order' => 3],
                ],
            ],
            'feedback_request' => [
                'name' => 'Feedback',
                'description' => 'Employee feedback form',
                'fields' => [
                    ['label' => 'Subject', 'field_key' => 'subject', 'field_type' => 'select', 'field_options' => 'General Inquiry,Attendance Issue,Leave Request Issue,Schedule Concern,Technical Problem,Suggestion,Complaint,Other', 'validation_rules' => 'required|string|max:255', 'is_required' => true, 'sort_order' => 1],
                    ['label' => 'Other Subject', 'field_key' => 'other_subject', 'field_type' => 'text', 'validation_rules' => 'nullable|string|max:255', 'is_required' => false, 'sort_order' => 2],
                    ['label' => 'Message', 'field_key' => 'message', 'field_type' => 'textarea', 'validation_rules' => 'required|string|max:2000', 'is_required' => true, 'sort_order' => 3],
                ],
            ],
        ];
    }
}
