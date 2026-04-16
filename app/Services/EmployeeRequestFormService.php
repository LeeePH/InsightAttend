<?php

namespace App\Services;

use App\Models\MaintenanceFormTemplate;

class EmployeeRequestFormService
{
    public function ensureDefaults(): void
    {
        $defaults = $this->defaultDefinitions();
        foreach ($defaults as $slug => $definition) {
            $template = MaintenanceFormTemplate::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_active' => true,
                ]
            );

            foreach ($definition['fields'] as $field) {
                $template->fields()->firstOrCreate(
                    ['field_key' => $field['field_key']],
                    $field
                );
            }
        }
    }

    public function getTemplate(string $slug): ?MaintenanceFormTemplate
    {
        return MaintenanceFormTemplate::with(['fields' => function ($query) {
            $query->orderBy('sort_order')->orderBy('id');
        }])->where('slug', $slug)->first();
    }

    public function getManagedTemplates()
    {
        return MaintenanceFormTemplate::with(['fields' => function ($query) {
            $query->orderBy('sort_order')->orderBy('id');
        }, 'creator'])->whereIn('slug', ['leave_request', 'resignation_request', 'feedback_request'])
            ->orderBy('name')
            ->get();
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

    public function fieldLabel(?MaintenanceFormTemplate $template, string $fieldKey, string $fallback): string
    {
        if (!$template) {
            return $fallback;
        }

        $field = $template->fields->firstWhere('field_key', $fieldKey);
        return $field && $field->label ? $field->label : $fallback;
    }

    public function fieldRequired(?MaintenanceFormTemplate $template, string $fieldKey, bool $fallback = false): bool
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

    public function selectOptions(?MaintenanceFormTemplate $template, string $fieldKey, array $fallback): array
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
    public function getMergedUiSettings(?MaintenanceFormTemplate $template, string $slug): array
    {
        $defaults = $this->defaultUiSettingsForSlug($slug);
        $saved = ($template && is_array($template->ui_settings)) ? $template->ui_settings : [];

        return array_merge($defaults, is_array($saved) ? $saved : []);
    }

    /**
     * Per-field presentation (placeholder, help, column width) for employee forms.
     */
    public function fieldUiMap(?MaintenanceFormTemplate $template, string $slug): array
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
            'accent' => '#2e3f5c',
            'accent_soft' => '#e8edf6',
            'bg' => '#f2f5fa',
            'text' => '#1f2a3d',
            'muted' => '#6b7587',
            'border' => '#dde3ed',
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
