<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MaintenanceFormField;
use App\Models\MaintenanceFormTemplate;
use App\Models\MaintenanceRecord;
use App\Services\EmployeeRequestFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    /** @var EmployeeRequestFormService */
    private $employeeRequestFormService;

    public function __construct(EmployeeRequestFormService $employeeRequestFormService)
    {
        $this->employeeRequestFormService = $employeeRequestFormService;
    }

    public function formBuilder(Request $request): View
    {
        $this->employeeRequestFormService->ensureDefaults();
        $templates = $this->employeeRequestFormService->getManagedTemplates();

        $selectedTemplate = null;
        $selectedTemplateId = (int) $request->query('template_id');
        if ($selectedTemplateId > 0) {
            $selectedTemplate = $templates->firstWhere('id', $selectedTemplateId);
        }

        if (!$selectedTemplate && $templates->count() > 0) {
            $selectedTemplate = $templates->first();
        }

        return view('admin.maintenance-form', compact('templates', 'selectedTemplate'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:maintenance_form_templates,name'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', 'unique:maintenance_form_templates,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $template = MaintenanceFormTemplate::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $this->buildUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        flash()->success('Success', 'Maintenance form template created.');
        return redirect()->route('admin.maintenance_form', ['template_id' => $template->id]);
    }

    public function updateTemplate(Request $request, MaintenanceFormTemplate $template): RedirectResponse
    {
        $coreSlugs = ['leave_request', 'resignation_request', 'feedback_request'];
        // Slug before any mutation — UI rules and merge must follow this template type even if slug is renamed later.
        $uiKind = in_array($template->slug, $coreSlugs, true) ? $template->slug : null;

        $rules = [
            'name' => ['required', 'string', 'max:120', Rule::unique('maintenance_form_templates', 'name')->ignore($template->id)],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('maintenance_form_templates', 'slug')->ignore($template->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($uiKind !== null) {
            $rules = array_merge($rules, [
                'ui_accent' => ['nullable', 'regex:/^#([0-9A-Fa-f]{6})$/'],
                'ui_accent_soft' => ['nullable', 'regex:/^#([0-9A-Fa-f]{6})$/'],
                'ui_bg' => ['nullable', 'regex:/^#([0-9A-Fa-f]{6})$/'],
                'ui_text' => ['nullable', 'regex:/^#([0-9A-Fa-f]{6})$/'],
                'ui_muted' => ['nullable', 'regex:/^#([0-9A-Fa-f]{6})$/'],
                'ui_border' => ['nullable', 'regex:/^#([0-9A-Fa-f]{6})$/'],
                'ui_card_radius' => ['nullable', 'integer', 'min:0', 'max:40'],
                'ui_submit_label' => ['nullable', 'string', 'max:120'],
                'ui_back_label_employee' => ['nullable', 'string', 'max:120'],
                'ui_back_label_guest' => ['nullable', 'string', 'max:120'],
                'ui_employee_panel_title' => ['nullable', 'string', 'max:120'],
            ]);
            if ($uiKind === 'leave_request') {
                $rules['ui_reason_footer_note'] = ['nullable', 'string', 'max:2000'];
            }
            if ($uiKind === 'resignation_request') {
                $rules['ui_notice_text'] = ['nullable', 'string', 'max:2000'];
            }
        }

        $validated = $request->validate($rules);

        $template->name = $validated['name'];
        $template->slug = $validated['slug'] ?? $this->buildUniqueSlug($validated['name'], $template->id);
        $template->description = $validated['description'] ?? null;
        $template->is_active = $request->boolean('is_active');
        $template->updated_by = auth()->id();

        if ($uiKind !== null) {
            $uiRaw = [
                'accent' => $validated['ui_accent'] ?? null,
                'accent_soft' => $validated['ui_accent_soft'] ?? null,
                'bg' => $validated['ui_bg'] ?? null,
                'text' => $validated['ui_text'] ?? null,
                'muted' => $validated['ui_muted'] ?? null,
                'border' => $validated['ui_border'] ?? null,
                'card_radius' => isset($validated['ui_card_radius']) ? (string) $validated['ui_card_radius'] : null,
                'submit_label' => $validated['ui_submit_label'] ?? null,
                'back_label_employee' => $validated['ui_back_label_employee'] ?? null,
                'back_label_guest' => $validated['ui_back_label_guest'] ?? null,
                'employee_panel_title' => $validated['ui_employee_panel_title'] ?? null,
                'show_employee_panel' => $request->boolean('ui_show_employee_panel'),
            ];
            if ($uiKind === 'leave_request') {
                $uiRaw['show_days_banner'] = $request->boolean('ui_show_days_banner');
                $uiRaw['reason_footer_note'] = $validated['ui_reason_footer_note'] ?? '';
            }
            if ($uiKind === 'resignation_request') {
                $uiRaw['show_notice_box'] = $request->boolean('ui_show_notice_box');
                $uiRaw['notice_text'] = $validated['ui_notice_text'] ?? '';
            }
            $template->ui_settings = $this->employeeRequestFormService->mergeUiSettingsFromInput($uiRaw, $uiKind);
        }

        $template->save();

        flash()->success('Success', 'Maintenance form template updated.');
        return redirect()->route('admin.maintenance_form', ['template_id' => $template->id]);
    }

    public function destroyTemplate(MaintenanceFormTemplate $template): RedirectResponse
    {
        if (in_array($template->slug, ['leave_request', 'resignation_request', 'feedback_request'])) {
            flash()->error('Error', 'Core employee request forms cannot be deleted.');
            return redirect()->route('admin.maintenance_form', ['template_id' => $template->id]);
        }

        $templateId = $template->id;
        $template->delete();

        flash()->success('Success', 'Maintenance form template deleted.');
        return redirect()->route('admin.maintenance_form', ['template_id' => $templateId]);
    }

    public function storeTemplateField(Request $request, MaintenanceFormTemplate $template): RedirectResponse
    {
        $validated = $this->validateTemplateField($request, $template);

        MaintenanceFormField::create([
            'maintenance_form_template_id' => $template->id,
            'label' => $validated['label'],
            'field_key' => $validated['field_key'],
            'field_type' => $validated['field_type'],
            'field_options' => $validated['field_options'] ?? null,
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'column_class' => $validated['column_class'] ?? null,
            'validation_rules' => $validated['validation_rules'] ?? null,
            'is_required' => $request->boolean('is_required'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        flash()->success('Success', 'Field added to template.');
        return redirect()->route('admin.maintenance_form', ['template_id' => $template->id]);
    }

    public function updateTemplateField(Request $request, MaintenanceFormTemplate $template, MaintenanceFormField $field): RedirectResponse
    {
        if ((int) $field->maintenance_form_template_id !== (int) $template->id) {
            abort(404);
        }

        $validated = $this->validateTemplateField($request, $template, $field->id);

        $field->update([
            'label' => $validated['label'],
            'field_key' => $validated['field_key'],
            'field_type' => $validated['field_type'],
            'field_options' => $validated['field_options'] ?? null,
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'column_class' => $validated['column_class'] ?? null,
            'validation_rules' => $validated['validation_rules'] ?? null,
            'is_required' => $request->boolean('is_required'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        flash()->success('Success', 'Template field updated.');
        return redirect()->route('admin.maintenance_form', ['template_id' => $template->id]);
    }

    public function destroyTemplateField(MaintenanceFormTemplate $template, MaintenanceFormField $field): RedirectResponse
    {
        if ((int) $field->maintenance_form_template_id !== (int) $template->id) {
            abort(404);
        }

        $field->delete();

        flash()->success('Success', 'Template field deleted.');
        return redirect()->route('admin.maintenance_form', ['template_id' => $template->id]);
    }

    public function index(): View
    {
        $employees = Employee::orderBy('name')->get(['id', 'name', 'position', 'employment_status']);
        $records = MaintenanceRecord::with(['employee', 'processor'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.maintenance', compact('employees', 'records'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'form_type' => ['required', 'in:departure,status_change'],
            'to_status' => ['required', 'in:active,inactive,on_leave,suspended,resigned,terminated,retired'],
            'effective_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $oldStatus = $employee->employment_status ?: 'active';
        $newStatus = $validated['to_status'];

        // Enforce consistency for departure forms.
        if ($validated['form_type'] === 'departure' && !in_array($newStatus, ['resigned', 'terminated', 'retired'])) {
            $newStatus = 'resigned';
        }

        $employee->employment_status = $newStatus;
        $employee->status_updated_by = auth()->id();

        if ($validated['form_type'] === 'departure') {
            $employee->departure_date = $validated['effective_date'] ?? now()->toDateString();
            $employee->departure_reason = $validated['reason'] ?? $validated['notes'] ?? null;
        } else {
            // Clear departure data if employee is moved back to non-departed status.
            if (!in_array($newStatus, ['resigned', 'terminated', 'retired'])) {
                $employee->departure_date = null;
                $employee->departure_reason = null;
            }
        }

        $employee->save();

        MaintenanceRecord::create([
            'employee_id' => $employee->id,
            'processed_by' => auth()->id(),
            'form_type' => $validated['form_type'],
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'effective_date' => $validated['effective_date'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        flash()->success('Success', 'Maintenance form submitted and employee status updated.');
        return back();
    }

    private function validateTemplateField(Request $request, MaintenanceFormTemplate $template, ?int $fieldId = null): array
    {
        $columnRule = Rule::in(['', 'col-12', 'col-md-12', 'col-md-8', 'col-md-6', 'col-md-4', 'col-lg-8', 'col-lg-6']);

        return $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'field_key' => [
                'required',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('maintenance_form_fields', 'field_key')
                    ->where(function ($query) use ($template) {
                        return $query->where('maintenance_form_template_id', $template->id);
                    })
                    ->ignore($fieldId),
            ],
            'field_type' => ['required', Rule::in(['text', 'textarea', 'number', 'date', 'datetime', 'select', 'checkbox', 'email'])],
            'field_options' => ['nullable', 'string', 'max:2000'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:2000'],
            'column_class' => ['nullable', 'string', 'max:40', $columnRule],
            'validation_rules' => ['nullable', 'string', 'max:500'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
    }

    private function buildUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = 'maintenance-form';
        }

        $slug = $baseSlug;
        $counter = 1;
        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = MaintenanceFormTemplate::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
