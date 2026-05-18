<!-- Add -->
<div class="modal fade employee-edit-modal" id="addnew" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 py-2 pr-3">
                <div></div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0 px-3 px-md-4 pb-3">
                <form method="POST" action="{{ route('employees.store') }}" id="employeeForm" novalidate>
                    @csrf
                    <div class="row align-items-start">
                        <div class="col-12 col-lg-7">
                            <div class="form-group mb-3">
                                <label class="d-block mb-1 font-weight-bold">Name</label>
                                <div class="form-row">
                                    <div class="col-sm-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Surname" id="add_emp_surname" name="surname" required autocomplete="family-name" maxlength="64" />
                                        <div class="add-emp-err text-danger small mt-1" data-for="surname" role="alert" style="display:none;"></div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <input type="text" class="form-control" placeholder="First name" id="add_emp_first_name" name="first_name" required autocomplete="given-name" maxlength="64" />
                                        <div class="add-emp-err text-danger small mt-1" data-for="first_name" role="alert" style="display:none;"></div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Middle name (optional)" id="add_emp_middle_name" name="middle_name" autocomplete="additional-name" maxlength="64" />
                                        <div class="add-emp-err text-danger small mt-1" data-for="middle_name" role="alert" style="display:none;"></div>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <input type="text" class="form-control" placeholder="Suffix (optional)" id="add_emp_suffix" name="suffix" autocomplete="honorific-suffix" maxlength="16" />
                                        <div class="add-emp-err text-danger small mt-1" data-for="suffix" role="alert" style="display:none;"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="add_emp_name" name="name" required />
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_position" class="font-weight-bold">Position</label>
                                    {{-- Datalist shown only when Admin Department is selected --}}
                                    <datalist id="add_emp_position_suggestions">
                                        <option value="Secretary - IT">
                                        <option value="Secretary - Educ">
                                        <option value="Secretary - SHTM">
                                    </datalist>
                                    {{-- Single input: type freely or pick from datalist suggestions --}}
                                    <input type="text" class="form-control" placeholder="Job title" id="add_emp_position" name="position" required autocomplete="organization-title" maxlength="64" />
                                    <div class="add-emp-err text-danger small mt-1" data-for="position" role="alert" style="display:none;"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_department" class="font-weight-bold">Department</label>
                                    <select class="form-control" id="add_emp_department" name="department_id" required>
                                        <option value="" selected>Select department</option>
                                        @foreach(($departments ?? []) as $dept)
                                            <option value="{{ $dept->id }}" data-name="{{ $dept->name }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="add-emp-err text-danger small mt-1" data-for="department_id" role="alert" style="display:none;"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_employee_number" class="font-weight-bold">Employee Number</label>
                                    <input type="text" class="form-control" id="add_emp_employee_number" name="employee_number"
                                           placeholder="e.g. 24-0001 (optional)"
                                           pattern="\d{2}-\d{4}"
                                           maxlength="20"
                                           title="Format: 2X-XXXX (e.g. 24-0001)">
                                    <div class="add-emp-err text-danger small mt-1" data-for="employee_number" role="alert" style="display:none;"></div>
                                </div>
                                <div class="col-md-6 mb-3" id="add_secretary_managed_wrap" style="display: none;">
                                    <label for="add_emp_managed_dept" class="font-weight-bold">Department management</label>
                                    <select class="form-control" id="add_emp_managed_dept" name="schedule_department_key">
                                        <option value="" selected>Select one</option>
                                        <option value="IT" {{ old('schedule_department_key') === 'IT' ? 'selected' : '' }}>IT</option>
                                        <option value="EDUC" {{ old('schedule_department_key') === 'EDUC' ? 'selected' : '' }}>EDUC</option>
                                        <option value="SHTM" {{ old('schedule_department_key') === 'SHTM' ? 'selected' : '' }}>SHTM</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Timetable scope for this secretary.</small>
                                    <div class="add-emp-err text-danger small mt-1" data-for="schedule_department_key" role="alert" style="display:none;"></div>
                                </div>
                            </div>
                            {{-- Hidden portal_role — always employee unless secretary dept is set --}}
                            <input type="hidden" name="portal_role" id="add_emp_portal_role" value="employee">
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_email" class="font-weight-bold">Email</label>
                                    <input type="email" class="form-control" id="add_emp_email" name="email" autocomplete="email" placeholder="youremail@gmail.com">
                                    <div class="add-emp-err text-danger small mt-1" data-for="email" role="alert" style="display:none;"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_phone" class="font-weight-bold">Phone (SMS)</label>
                                    <input type="text" class="form-control" id="add_emp_phone" name="phone" placeholder="+639">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_date_hired" class="font-weight-bold">Date hired</label>
                                    <input type="date" class="form-control" id="add_emp_date_hired" name="date_hired" value="{{ old('date_hired', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_employment_type" class="font-weight-bold">Employment Status</label>
                                    <select class="form-control" id="add_emp_employment_type" name="employment_type">
                                        <option value="">Select</option>
                                        <option value="regular">Regular Employee</option>
                                        <option value="probationary">Probationary</option>
                                        <option value="consultant">Consultant</option>
                                        <option value="trainee">Trainee</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="add_emp_password" class="font-weight-bold">Login password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="add_emp_password" name="password" placeholder="Optional — min. 8 characters" autocomplete="new-password" minlength="8">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="toggleAddPassword" tabindex="-1" title="Show/hide password">
                                            <i class="fa fa-eye" id="toggleAddPasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">If set, email is required. Role applies to that login.</small>
                                <div class="add-emp-err text-danger small mt-1" data-for="password" role="alert" style="display:none;"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="add_emp_educational_background" class="font-weight-bold">Educational Background</label>
                                <textarea class="form-control" id="add_emp_educational_background" name="educational_background" rows="2" placeholder="One entry per line (e.g. BS Computer Science, University of Santo Tomas)"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="add_emp_work_experience" class="font-weight-bold">Work Experience</label>
                                <textarea class="form-control" id="add_emp_work_experience" name="work_experience" rows="2" placeholder="One entry per line (e.g. Software Engineer at Acme Corp, 2020-2023)"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="col-md-4 mb-3">
                                    <label for="add_emp_emergency_name" class="font-weight-bold">Emergency contact</label>
                                    <input type="text" class="form-control" id="add_emp_emergency_name" name="emergency_contact_name" placeholder="Name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="add_emp_emergency_relationship" class="font-weight-bold">Relationship</label>
                                    <input type="text" class="form-control" id="add_emp_emergency_relationship" name="emergency_contact_relationship" placeholder="e.g. Spouse">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="add_emp_emergency_phone" class="font-weight-bold">Emergency phone</label>
                                    <input type="text" class="form-control" id="add_emp_emergency_phone" name="emergency_contact_phone" placeholder="Phone">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5 mt-3 mt-lg-0">
                            <div class="border rounded p-3 bg-light h-100">
                                <label class="font-weight-bold d-block mb-2">Face registration</label>
                                <div id="faceCaptureContainer" class="mx-auto position-relative bg-white rounded border border-primary" style="width: 320px; height: 240px; max-width: 100%;">
                                    <video id="video" width="320" height="240" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 4px;"></video>
                                    <canvas id="canvas" width="320" height="240" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; border-radius: 4px;"></canvas>
                                </div>
                                <div class="text-center mt-2">
                                    <button type="button" id="startCamera" class="btn btn-info btn-sm mb-1">
                                        <i class="fa fa-camera"></i> Start camera
                                    </button>
                                    <button type="button" id="captureFace" class="btn btn-primary btn-sm mb-1" disabled>
                                        <i class="fa fa-camera-retro"></i> Capture
                                    </button>
                                    <button type="button" id="retakeFace" class="btn btn-warning btn-sm mb-1" style="display: none;">
                                        <i class="fa fa-refresh"></i> Retake
                                    </button>
                                </div>
                                <input type="hidden" id="face_descriptor" name="face_descriptor">
                                <input type="hidden" id="face_image" name="face_image">
                                <div id="faceStatus" class="alert alert-info py-2 small mb-0 mt-2" style="display: none;">Position your face in the frame, then capture.</div>
                                <div id="faceSuccess" class="alert alert-success py-2 small mb-0 mt-2" style="display: none;"><i class="fa fa-check"></i> Face saved.</div>
                                <div class="add-emp-err text-danger small mt-2" data-for="face" role="alert" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end border-top pt-3 mt-2">
                        <button type="button" class="btn btn-outline-secondary mr-2 mb-1" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary mb-1" id="submitBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var btn  = document.getElementById('toggleAddPassword');
    var inp  = document.getElementById('add_emp_password');
    var icon = document.getElementById('toggleAddPasswordIcon');
    if (btn && inp && icon) {
        btn.addEventListener('click', function () {
            var isHidden = inp.type === 'password';
            inp.type = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'fa fa-eye-slash' : 'fa fa-eye';
        });
    }
})();
</script>

<script>
(function () {
    var deptSel     = document.getElementById('add_emp_department');
    var posText     = document.getElementById('add_emp_position');
    var roleInput   = document.getElementById('add_emp_portal_role');
    var managedWrap = document.getElementById('add_secretary_managed_wrap');
    var managedDept = document.getElementById('add_emp_managed_dept');
    var datalist    = document.getElementById('add_emp_position_suggestions');

    var secretaryDeptMap = {
        'Secretary - IT':   'IT',
        'Secretary - Educ': 'EDUC',
        'Secretary - SHTM': 'SHTM'
    };

    function isAdminDept() {
        var opt = deptSel ? deptSel.options[deptSel.selectedIndex] : null;
        return opt && opt.getAttribute('data-name') === 'Admin Department';
    }

    // Attach or detach the datalist based on department
    function syncDatalist() {
        if (!posText || !datalist) return;
        if (isAdminDept()) {
            posText.setAttribute('list', 'add_emp_position_suggestions');
        } else {
            posText.removeAttribute('list');
        }
    }

    // When a Secretary suggestion is picked, auto-set role and dept management
    function onPositionInput() {
        var val = posText ? posText.value : '';
        var deptKey = secretaryDeptMap[val] || '';
        if (!roleInput || !managedWrap || !managedDept) return;
        if (deptKey && isAdminDept()) {
            roleInput.value = 'secretary';
            managedDept.value = deptKey;
            managedWrap.style.display = '';
        } else if (!deptKey && isAdminDept()) {
            // typed something custom — clear secretary auto-assignment
            roleInput.value = 'employee';
            managedDept.value = '';
            managedWrap.style.display = 'none';
        }
        if (typeof window.refreshEmployeeAddValidation === 'function') {
            window.refreshEmployeeAddValidation();
        }
    }

    if (deptSel) {
        deptSel.addEventListener('change', function () {
            syncDatalist();
            // Clear secretary state when switching away from Admin
            if (!isAdminDept() && roleInput) {
                roleInput.value = 'employee';
                if (managedDept) managedDept.value = '';
                if (managedWrap) managedWrap.style.display = 'none';
            }
        });
    }
    if (posText) {
        posText.addEventListener('input', onPositionInput);
        posText.addEventListener('change', onPositionInput);
    }

    syncDatalist();
})();
</script>
