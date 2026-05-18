@php
    $entry = $entry ?? null;
    $timeBlocks = collect(old('time_start_blocks', $entry ? collect($entry->resolvedTimeBlocks())->pluck('time_start')->all() : ['']))
        ->values();
    $timeEnds = collect(old('time_end_blocks', $entry ? collect($entry->resolvedTimeBlocks())->pluck('time_end')->all() : ['']))
        ->values();
    $rowCount = max($timeBlocks->count(), $timeEnds->count(), 1);
@endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Faculty / Employee</label>
        <select name="employee_id" class="form-control" required>
            <option value="">- Select -</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ (int) old('employee_id', $entry->employee_id ?? 0) === (int) $emp->id ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Course Description</label>
        <select name="subject_id" class="form-control js-timetable-course" required>
            <option value="">- Select Subject -</option>
            @foreach($subjects as $subject)
                @php
                    $prefix = strtoupper(preg_replace('/[\s\d].*/', '', trim($subject->code)));
                    $subjectDept = match(true) {
                        $prefix === 'IT'   => 'IT',
                        $prefix === 'EDUC' => 'EDUC',
                        $prefix === 'SHTM' => 'SHTM',
                        default            => 'GE',
                    };
                @endphp
                <option
                    value="{{ $subject->id }}"
                    data-dept="{{ $subjectDept }}"
                    {{ (int) old('subject_id', $entry->subject_id ?? 0) === (int) $subject->id ? 'selected' : '' }}
                >
                    {{ $subject->code }} - {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label>Class section</label>
        <select name="class_section_id" class="form-control js-timetable-class-section" @if(($classSections ?? collect())->isNotEmpty()) required @endif>
            <option value="">- Select section -</option>
            @foreach(($classSections ?? collect()) as $sec)
                <option
                    value="{{ $sec->id }}"
                    data-department="{{ $sec->department_key }}"
                    {{ (int) old('class_section_id', $entry->class_section_id ?? 0) === (int) $sec->id ? 'selected' : '' }}
                >
                    {{ $sec->section_label }}
                    @if($sec->year_level)
                        (Y{{ $sec->year_level }})
                    @endif
                    — {{ $sec->department_key }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Manage sections under <a href="{{ route('class_sections.index') }}" target="_blank">Class sections</a>.</small>
    </div>
</div>

{{-- department_key is derived automatically from the selected section --}}
<input type="hidden" name="department_key" class="js-timetable-department-key" value="{{ old('department_key', $entry->department_key ?? '') }}">

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Day</label>
        <select name="day_of_week" class="form-control" required>
            @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $num => $label)
                <option value="{{ $num }}" {{ (int) old('day_of_week', $entry->day_of_week ?? 1) === $num ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Room</label>
        <input type="text" name="room" class="form-control" value="{{ old('room', $entry->room ?? '') }}" placeholder="e.g. Lab-1, PB208" required maxlength="64">
    </div>
</div>

<div class="form-group">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="mb-0">Schedule Time Blocks</label>
        <button type="button" class="btn btn-sm btn-outline-primary js-add-time-block">
            <i class="fa fa-plus"></i> Add Time
        </button>
    </div>

    <div class="js-time-blocks">
        @for ($i = 0; $i < $rowCount; $i++)
            <div class="form-row align-items-end js-time-block-row mb-2">
                <div class="form-group col-md-5 mb-0">
                    <label class="small text-muted">Time Start</label>
                    <input type="time" name="time_start_blocks[]" class="form-control" value="{{ $timeBlocks[$i] ?? '' }}" required>
                </div>
                <div class="form-group col-md-5 mb-0">
                    <label class="small text-muted">Time End</label>
                    <input type="time" name="time_end_blocks[]" class="form-control" value="{{ $timeEnds[$i] ?? '' }}" required>
                </div>
                <div class="form-group col-md-2 mb-0">
                    <button type="button" class="btn btn-outline-danger btn-block js-remove-time-block" {{ $i === 0 ? 'disabled' : '' }}>
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        @endfor
    </div>

    <small class="text-muted">Use the + button to add another start/end time for the same day. Gaps between time blocks naturally act as breaktime.</small>
    @error('time_start_blocks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @error('time_end_blocks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
