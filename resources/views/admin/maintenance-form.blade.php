@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Maintenance Form</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Maintenance Form</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-3">Employee request forms</h4>
                    <p class="text-muted small mb-3">Configure field labels, validation, and order here. For <strong>Leave</strong> and <strong>Resignation</strong> templates, use the appearance section below to tune colors, button text, and helper copy on the employee-facing pages.</p>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Fields</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td><code>{{ $template->slug }}</code></td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->fields->count() }}</td>
                                        <td>
                                            <a href="{{ route('admin.maintenance_form', ['template_id' => $template->id]) }}" class="btn btn-info btn-sm">Open</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No templates yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($selectedTemplate)
        @php
            $empFormUi = app(\App\Services\EmployeeRequestFormService::class)->getMergedUiSettings($selectedTemplate, $selectedTemplate->slug);
        @endphp
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mt-0 header-title mb-3">Edit template: {{ $selectedTemplate->name }}</h4>
                        <form method="POST" action="{{ route('admin.maintenance_form.templates.update', $selectedTemplate) }}">
                            @csrf
                            @method('PUT')
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $selectedTemplate->name }}" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Slug</label>
                                    <input type="text" name="slug" class="form-control" value="{{ $selectedTemplate->slug }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Description <span class="text-muted font-weight-normal">(subtitle on employee page)</span></label>
                                    <input type="text" name="description" class="form-control" value="{{ $selectedTemplate->description }}">
                                </div>
                                <div class="form-group col-md-1">
                                    <label>Status</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1" {{ $selectedTemplate->is_active ? 'selected' : '' }}>On</option>
                                        <option value="0" {{ !$selectedTemplate->is_active ? 'selected' : '' }}>Off</option>
                                    </select>
                                </div>
                            </div>

                            @if(in_array($selectedTemplate->slug, ['leave_request', 'resignation_request', 'feedback_request'], true))
                                <hr class="my-4">
                                <h5 class="mb-3">Employee page appearance</h5>
                                <p class="text-muted small">Colors must be <code>#RRGGBB</code> (six hex digits). Leave blank to keep the current saved value; use the color pickers to change.</p>
                                <div class="form-row">
                                    <div class="form-group col-md-2">
                                        <label>Accent</label>
                                        <input type="color" class="form-control" name="ui_accent_picker" value="{{ $empFormUi['accent'] }}" oninput="this.form.ui_accent.value=this.value">
                                        <input type="hidden" name="ui_accent" value="{{ $empFormUi['accent'] }}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Accent soft</label>
                                        <input type="color" class="form-control" name="ui_accent_soft_picker" value="{{ $empFormUi['accent_soft'] }}" oninput="this.form.ui_accent_soft.value=this.value">
                                        <input type="hidden" name="ui_accent_soft" value="{{ $empFormUi['accent_soft'] }}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Page background</label>
                                        <input type="color" class="form-control" name="ui_bg_picker" value="{{ $empFormUi['bg'] }}" oninput="this.form.ui_bg.value=this.value">
                                        <input type="hidden" name="ui_bg" value="{{ $empFormUi['bg'] }}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Text</label>
                                        <input type="color" class="form-control" name="ui_text_picker" value="{{ $empFormUi['text'] }}" oninput="this.form.ui_text.value=this.value">
                                        <input type="hidden" name="ui_text" value="{{ $empFormUi['text'] }}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Muted text</label>
                                        <input type="color" class="form-control" name="ui_muted_picker" value="{{ $empFormUi['muted'] }}" oninput="this.form.ui_muted.value=this.value">
                                        <input type="hidden" name="ui_muted" value="{{ $empFormUi['muted'] }}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Borders</label>
                                        <input type="color" class="form-control" name="ui_border_picker" value="{{ $empFormUi['border'] }}" oninput="this.form.ui_border.value=this.value">
                                        <input type="hidden" name="ui_border" value="{{ $empFormUi['border'] }}">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-2">
                                        <label>Card radius (px)</label>
                                        <input type="number" name="ui_card_radius" class="form-control" min="0" max="40" value="{{ (int) ($empFormUi['card_radius'] ?? 14) }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Submit button</label>
                                        <input type="text" name="ui_submit_label" class="form-control" value="{{ $empFormUi['submit_label'] }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Back (logged-in)</label>
                                        <input type="text" name="ui_back_label_employee" class="form-control" value="{{ $empFormUi['back_label_employee'] }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Back (guest leave)</label>
                                        <input type="text" name="ui_back_label_guest" class="form-control" value="{{ $empFormUi['back_label_guest'] }}">
                                    </div>
                                </div>
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-4 mb-0">
                                        <label>Employee panel title</label>
                                        <input type="text" name="ui_employee_panel_title" class="form-control" value="{{ $empFormUi['employee_panel_title'] }}">
                                    </div>
                                    <div class="form-group col-md-2 mb-0">
                                        <input type="hidden" name="ui_show_employee_panel" value="0">
                                        <div class="custom-control custom-checkbox mt-4">
                                            <input type="checkbox" class="custom-control-input" id="ui_show_employee_panel" name="ui_show_employee_panel" value="1" {{ !empty($empFormUi['show_employee_panel']) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="ui_show_employee_panel">Show employee panel</label>
                                        </div>
                                    </div>
                                    @if($selectedTemplate->slug === 'leave_request')
                                        <div class="form-group col-md-2 mb-0">
                                            <input type="hidden" name="ui_show_days_banner" value="0">
                                            <div class="custom-control custom-checkbox mt-4">
                                                <input type="checkbox" class="custom-control-input" id="ui_show_days_banner" name="ui_show_days_banner" value="1" {{ !empty($empFormUi['show_days_banner']) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ui_show_days_banner">Show day counter</label>
                                            </div>
                                        </div>
                                    @endif
                                    @if($selectedTemplate->slug === 'resignation_request')
                                        <div class="form-group col-md-2 mb-0">
                                            <input type="hidden" name="ui_show_notice_box" value="0">
                                            <div class="custom-control custom-checkbox mt-4">
                                                <input type="checkbox" class="custom-control-input" id="ui_show_notice_box" name="ui_show_notice_box" value="1" {{ !empty($empFormUi['show_notice_box']) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="ui_show_notice_box">Show notice box</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if($selectedTemplate->slug === 'leave_request')
                                    <div class="form-group">
                                        <label>Note under reason <span class="text-muted font-weight-normal">(e.g. medical certificate policy)</span></label>
                                        <textarea name="ui_reason_footer_note" class="form-control" rows="2">{{ $empFormUi['reason_footer_note'] }}</textarea>
                                    </div>
                                @endif
                                @if($selectedTemplate->slug === 'resignation_request')
                                    <div class="form-group">
                                        <label>Notice box text</label>
                                        <textarea name="ui_notice_text" class="form-control" rows="2">{{ $empFormUi['notice_text'] }}</textarea>
                                    </div>
                                @endif
                            @endif

                            <button type="submit" class="btn btn-primary btn-sm">Save template</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mt-0 header-title mb-3">Add field</h4>
                        <p class="text-muted small">For leave/resignation, <strong>column class</strong> controls width on the employee form (Bootstrap grid). <strong>Help text</strong> appears under the input.</p>
                        <form method="POST" action="{{ route('admin.maintenance_form.fields.store', $selectedTemplate) }}">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label>Label</label>
                                    <input type="text" name="label" class="form-control" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Field Key</label>
                                    <input type="text" name="field_key" class="form-control" placeholder="employee_code" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Type</label>
                                    <select name="field_type" class="form-control" required>
                                        <option value="text">text</option>
                                        <option value="textarea">textarea</option>
                                        <option value="number">number</option>
                                        <option value="date">date</option>
                                        <option value="datetime">datetime</option>
                                        <option value="select">select</option>
                                        <option value="checkbox">checkbox</option>
                                        <option value="email">email</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Options</label>
                                    <input type="text" name="field_options" class="form-control" placeholder="a,b,c">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Placeholder</label>
                                    <input type="text" name="placeholder" class="form-control" placeholder="Optional">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Column</label>
                                    <select name="column_class" class="form-control">
                                        <option value="">(default)</option>
                                        <option value="col-12">col-12</option>
                                        <option value="col-md-12">col-md-12</option>
                                        <option value="col-md-8">col-md-8</option>
                                        <option value="col-md-6">col-md-6</option>
                                        <option value="col-md-4">col-md-4</option>
                                        <option value="col-lg-8">col-lg-8</option>
                                        <option value="col-lg-6">col-lg-6</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Help text</label>
                                    <input type="text" name="help_text" class="form-control" placeholder="Shown under the field on the employee form">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Validation Rules</label>
                                    <input type="text" name="validation_rules" class="form-control" placeholder="nullable|max:255">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Required</label>
                                    <select name="is_required" class="form-control">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Field
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mt-0 header-title mb-3">Fields</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Field Key</th>
                                        <th>Type</th>
                                        <th>Options</th>
                                        <th>Placeholder</th>
                                        <th>Help</th>
                                        <th>Col</th>
                                        <th>Validation Rules</th>
                                        <th>Required</th>
                                        <th>Order</th>
                                        <th width="260">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedTemplate->fields as $field)
                                        <tr>
                                            <td colspan="11" class="p-0 border-bottom">
                                                <div class="d-flex flex-wrap align-items-start p-2">
                                                    <form method="POST" action="{{ route('admin.maintenance_form.fields.update', ['template' => $selectedTemplate->id, 'field' => $field->id]) }}" class="flex-grow-1 mr-2 mb-0">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="form-row">
                                                            <div class="form-group col-md-2 col-sm-6 mb-1">
                                                                <label class="small text-muted mb-0">Label</label>
                                                                <input type="text" name="label" class="form-control form-control-sm" value="{{ $field->label }}" required>
                                                            </div>
                                                            <div class="form-group col-md-2 col-sm-6 mb-1">
                                                                <label class="small text-muted mb-0">Field key</label>
                                                                <input type="text" name="field_key" class="form-control form-control-sm" value="{{ $field->field_key }}" required>
                                                            </div>
                                                            <div class="form-group col-md-1 col-sm-4 mb-1">
                                                                <label class="small text-muted mb-0">Type</label>
                                                                <select name="field_type" class="form-control form-control-sm">
                                                                    @foreach(['text', 'textarea', 'number', 'date', 'datetime', 'select', 'checkbox', 'email'] as $type)
                                                                        <option value="{{ $type }}" {{ $field->field_type === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-2 col-sm-6 mb-1">
                                                                <label class="small text-muted mb-0">Options</label>
                                                                <input type="text" name="field_options" class="form-control form-control-sm" value="{{ $field->field_options }}">
                                                            </div>
                                                            <div class="form-group col-md-2 col-sm-6 mb-1">
                                                                <label class="small text-muted mb-0">Placeholder</label>
                                                                <input type="text" name="placeholder" class="form-control form-control-sm" value="{{ $field->placeholder }}">
                                                            </div>
                                                            <div class="form-group col-md-2 col-sm-6 mb-1">
                                                                <label class="small text-muted mb-0">Help</label>
                                                                <input type="text" name="help_text" class="form-control form-control-sm" value="{{ $field->help_text }}">
                                                            </div>
                                                            <div class="form-group col-md-1 col-sm-4 mb-1">
                                                                <label class="small text-muted mb-0">Col</label>
                                                                <select name="column_class" class="form-control form-control-sm">
                                                                    @foreach(['', 'col-12', 'col-md-12', 'col-md-8', 'col-md-6', 'col-md-4', 'col-lg-8', 'col-lg-6'] as $colOpt)
                                                                        <option value="{{ $colOpt }}" {{ ($field->column_class ?? '') === $colOpt ? 'selected' : '' }}>{{ $colOpt ?: '—' }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-2 col-sm-6 mb-1">
                                                                <label class="small text-muted mb-0">Validation</label>
                                                                <input type="text" name="validation_rules" class="form-control form-control-sm" value="{{ $field->validation_rules }}">
                                                            </div>
                                                            <div class="form-group col-md-1 col-sm-4 mb-1">
                                                                <label class="small text-muted mb-0">Req</label>
                                                                <select name="is_required" class="form-control form-control-sm">
                                                                    <option value="0" {{ !$field->is_required ? 'selected' : '' }}>No</option>
                                                                    <option value="1" {{ $field->is_required ? 'selected' : '' }}>Yes</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-1 col-sm-4 mb-1">
                                                                <label class="small text-muted mb-0">Order</label>
                                                                <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $field->sort_order }}" min="0">
                                                            </div>
                                                            <div class="form-group col-md-12 mb-0 mt-1">
                                                                <button type="submit" class="btn btn-primary btn-sm">Save field</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.maintenance_form.fields.destroy', ['template' => $selectedTemplate->id, 'field' => $field->id]) }}" class="d-inline mb-0" onsubmit="return confirm('Delete this field?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">No fields yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
