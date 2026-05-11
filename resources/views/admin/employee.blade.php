@extends('layouts.master')

@section('css')
<style>
    .employee-list-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 0;
    }
    #datatable-buttons {
        width: 100% !important;
        margin-bottom: 0;
    }
    #datatable-buttons th,
    #datatable-buttons td {
        font-size: 0.82rem;
        vertical-align: middle;
    }
    #datatable-buttons th:not(:last-child),
    #datatable-buttons td:not(:last-child) {
        white-space: nowrap;
    }
    #datatable-buttons th:last-child,
    #datatable-buttons td:last-child {
        white-space: normal !important;
        min-width: 220px;
    }
    #datatable-buttons th:nth-child(1) { width: 14%; }
    #datatable-buttons th:nth-child(2) { width: 12%; }
    #datatable-buttons th:nth-child(3) { width: 12%; }
    #datatable-buttons th:nth-child(4) { width: 16%; }
    #datatable-buttons th:nth-child(5) { width: 10%; }
    #datatable-buttons th:nth-child(6) { width: 12%; }
    #datatable-buttons th:nth-child(7) { width: 12%; }
    #datatable-buttons th:nth-child(8) { width: 12%; }
    table.dataTable.dtr-inline.collapsed > tbody > tr[role="row"] > td:first-child:before,
    table.dataTable.dtr-inline.collapsed > tbody > tr[role="row"] > th:first-child:before {
        display: none !important;
    }
    .employee-action-group {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 0.25rem;
    }
    .employee-action-group .btn {
        margin-right: 0;
        margin-bottom: 0;
        padding: 0.22rem 0.42rem;
        font-size: 0.74rem;
        white-space: nowrap !important;
    }
    .employee-profile-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
    }
    .employee-profile-modal .profile-hero {
        background: linear-gradient(135deg, #8B4513 0%, #b66a33 100%);
        color: #fff;
        padding: 1.5rem;
    }
    .employee-profile-modal .profile-avatar {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
    .employee-profile-modal .info-card {
        border: 1px solid #ecd9ca;
        border-radius: 14px;
        background: #fffdfa;
        padding: 1rem;
        height: 100%;
    }
    .employee-profile-modal .info-label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8a6a55;
        margin-bottom: 0.3rem;
    }
    .employee-profile-modal .info-value {
        color: #3e2412;
        font-weight: 600;
        word-break: break-word;
    }
    .employee-edit-modal .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
    }
    .employee-edit-modal .edit-modal-hero {
        background: linear-gradient(135deg, #5a3d2e 0%, #8B5A2B 100%);
        color: #fff;
        padding: 1rem 1.25rem;
    }
    .employee-edit-modal .edit-modal-hero h5 {
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .employee-edit-modal .edit-section-title {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8a6a55;
        font-weight: 700;
        margin: 1.25rem 0 0.65rem;
    }
    .employee-edit-modal .edit-section-title:first-of-type {
        margin-top: 0;
    }
    .employee-edit-modal .form-control:focus {
        border-color: #b66a33;
        box-shadow: 0 0 0 0.15rem rgba(139, 90, 43, 0.18);
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Employees</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Employees</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Employees List</a></li>
  
    </ol>
</div>
@endsection
@section('button')
<a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">Add</a>
        

@endsection

@section('content')
@include('includes.flash')
<!--Show Validation Errors here-->
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<!--End showing Validation Errors here-->


                      <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                                @php
                                                    $deptOptions = [];
                                                    foreach ($employees as $emp) {
                                                        $deptName = $emp->department?->name ?? null;
                                                        if ($deptName) {
                                                            $deptOptions[$deptName] = true;
                                                        }
                                                    }
                                                    $deptOptions = array_keys($deptOptions);
                                                    sort($deptOptions, SORT_NATURAL | SORT_FLAG_CASE);
                                                @endphp

                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label for="employeeDepartmentFilter" class="mb-1">Filter by Department</label>
                                                        <select id="employeeDepartmentFilter" class="form-control">
                                                            <option value="" selected>All Departments</option>
                                                            @foreach ($deptOptions as $deptOpt)
                                                                <option value="{{ $deptOpt }}">{{ $deptOpt }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="table-responsive employee-list-table-wrap">
                                                <table id="datatable-buttons" class="table table-striped table-bordered" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        
                                                    <thead>
                                                    <tr>
                                                        <th data-priority="1">Name</th>
                                                        <th data-priority="2">Position</th>
                                                        <th data-priority="3">Department</th>
                                                        <th data-priority="4">Email</th>
                                                        <th data-priority="4">Role</th>
                                                        <th data-priority="4">Schedule</th>
                                                        <th data-priority="5">Member Since</th>
                                                        <th data-priority="1">Actions</th>
                                                      
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach( $employees as $employee)

                                                        <tr>
                                                            <td>{{$employee->name}}</td>
                                                            <td>{{$employee->position}}</td>
                                                            <td>
                                                                {{ $employee->department?->name ?? 'N/A' }}
                                                            </td>
                                                            <td>{{ $employee->email ?: '—' }}</td>
                                                            <td>
                                                                @php $loginUser = $employee->user; @endphp
                                                                @if (!$loginUser)
                                                                    <span class="text-muted">No login</span>
                                                                @elseif ($loginUser->hasRole('secretary'))
                                                                    <span class="badge badge-info">Secretary</span>
                                                                @elseif ($loginUser->hasRole('employee'))
                                                                    <span class="badge badge-secondary">Employee</span>
                                                                @else
                                                                    <span class="badge badge-dark" title="Managed under User Management">{{ $loginUser->roles->first()->name ?? 'User' }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(isset($employee->schedules->first()->slug))
                                                                {{$employee->schedules->first()->slug}}
                                                                @endif
                                                            </td>
                                                            <td title="{{ $employee->created_at }}">
                                                                {{ \Carbon\Carbon::parse($employee->created_at)->format('M d, Y h:i A') }}
                                                            </td>
                                                            <td class="employee-action-group">
                                                                <a href="{{ route('employees.profile', $employee->id) }}" class="btn btn-info btn-sm btn-flat"><i class='fa fa-eye'></i> View</a>
                                                                <a href="#edit-employee-{{ $employee->id }}" data-toggle="modal" class="btn btn-success btn-sm edit btn-flat"><i class='fa fa-edit'></i> Edit</a>
                                                                <a href="#delete-employee-{{ $employee->id }}" data-toggle="modal" class="btn btn-danger btn-sm delete btn-flat"><i class='fa fa-trash'></i> Delete</a>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                   
                                                    </tbody>
                                                </table>
                                                </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                    

@foreach( $employees as $employee)
@include('includes.edit_delete_employee')
@endforeach

@include('includes.add_employee')

@endsection


@section('script')
<!-- Face API JS -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    (function () {
        function escapeRegex(str) {
            return (str || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function getDeptCellText(rowEl) {
            if (!rowEl || !rowEl.cells || rowEl.cells.length < 3) return '';
            return (rowEl.cells[2].textContent || '').trim();
        }

        function applyDepartmentFilter(value) {
            var tableEl = document.getElementById('datatable-buttons');
            if (!tableEl) return;

            // Prefer DataTables column search if available
            if (window.jQuery && jQuery.fn && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable) {
                try {
                    if (jQuery.fn.dataTable.isDataTable('#datatable-buttons')) {
                        var dt = jQuery('#datatable-buttons').DataTable();
                        if (!value) {
                            dt.column(2).search('', true, false).draw();
                        } else {
                            dt.column(2).search('^' + escapeRegex(value) + '$', true, false).draw();
                        }
                        return;
                    }
                } catch (e) {
                    // fall through to non-DataTables filtering
                }
            }

            // Fallback: simple row show/hide
            var tbody = tableEl.tBodies && tableEl.tBodies[0];
            if (!tbody) return;
            Array.prototype.forEach.call(tbody.rows, function (tr) {
                var deptText = getDeptCellText(tr);
                tr.style.display = (!value || deptText === value) ? '' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var sel = document.getElementById('employeeDepartmentFilter');
            if (!sel) return;
            sel.addEventListener('change', function () {
                applyDepartmentFilter(sel.value);
            });

        });
    })();

    let video = document.getElementById('video');
    let canvas = document.getElementById('canvas');
    let startCameraBtn = document.getElementById('startCamera');
    let captureFaceBtn = document.getElementById('captureFace');
    let retakeFaceBtn = document.getElementById('retakeFace');
    let faceStatus = document.getElementById('faceStatus');
    let faceSuccess = document.getElementById('faceSuccess');
    let faceDescriptorInput = document.getElementById('face_descriptor');
    let faceImageInput = document.getElementById('face_image');
    let modelsLoaded = false;
    let capturedDescriptor = null;

    (function () {
        const employeeForm = document.getElementById('employeeForm');
        if (!employeeForm) return;

        const SUFFIX_RE = /^[A-Za-z0-9][A-Za-z0-9.\s'-]*$/;
        const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const PASS_MIN = 8;

        let addEmpSubmitAttempted = false;
        const addEmpTouched = {};

        function addEmpField(name) {
            return employeeForm.querySelector('[name="' + name + '"]');
        }

        function addEmpSetError(key, message) {
            const err = employeeForm.querySelector('.add-emp-err[data-for="' + key + '"]');
            const input = key === 'face' ? null : addEmpField(key);
            if (err) {
                if (message) {
                    err.textContent = message;
                    err.style.display = 'block';
                } else {
                    err.textContent = '';
                    err.style.display = 'none';
                }
            }
            if (input) {
                if (message) input.classList.add('is-invalid');
                else input.classList.remove('is-invalid');
            }
        }

        function addEmpShowRequired(key) {
            return addEmpSubmitAttempted || !!addEmpTouched[key];
        }

        function buildEmployeeFullName() {
            const surname = (addEmpField('surname') ? addEmpField('surname').value : '').trim();
            const first = (addEmpField('first_name') ? addEmpField('first_name').value : '').trim();
            const middle = (addEmpField('middle_name') ? addEmpField('middle_name').value : '').trim();
            const suffix = (addEmpField('suffix') ? addEmpField('suffix').value : '').trim();

            if (!surname && !first && !middle && !suffix) return '';

            let right = first;
            if (middle) right += (right ? ' ' : '') + middle;
            if (suffix) right += (right ? ' ' : '') + suffix;

            if (surname && right) return surname + ', ' + right;
            if (surname) return surname;
            return right;
        }

        function syncEmployeeFullName() {
            const nameEl = addEmpField('name');
            if (!nameEl) return;
            nameEl.value = buildEmployeeFullName();
        }

        function validateAddEmpSurname(value) {
            const t = (value || '').trim();
            if (t.length === 0) {
                return addEmpShowRequired('surname') ? 'Surname is required.' : '';
            }
            if (t.length < 2) return 'Surname must be at least 2 characters.';
            if (t.length > 64) return 'Surname must not exceed 64 characters.';
            return '';
        }

        function validateAddEmpFirstName(value) {
            const t = (value || '').trim();
            if (t.length === 0) {
                return addEmpShowRequired('first_name') ? 'First Name is required.' : '';
            }
            if (t.length < 2) return 'First Name must be at least 2 characters.';
            if (t.length > 64) return 'First Name must not exceed 64 characters.';
            return '';
        }

        function validateAddEmpMiddleName(value) {
            const t = (value || '').trim();
            if (t.length === 0) return '';
            if (t.length > 64) return 'Middle Name must not exceed 64 characters.';
            return '';
        }

        function validateAddEmpSuffix(value) {
            const t = (value || '').trim();
            if (t.length === 0) return '';
            if (t.length > 16) return 'Suffix must not exceed 16 characters.';
            if (!SUFFIX_RE.test(t)) return 'Suffix contains invalid characters.';
            return '';
        }

        function validateAddEmpName(value) {
            const t = (value || '').trim();
            if (t.length === 0) {
                return addEmpShowRequired('name') ? 'Name is required.' : '';
            }
            if (t.length > 128) return 'Name is too long.';
            return '';
        }

        function validateAddEmpPosition(value) {
            const t = (value || '').trim();
            if (t.length === 0) {
                return addEmpShowRequired('position') ? 'Position is required.' : '';
            }
            if (t.length < 2) return 'Position must be at least 2 characters.';
            if (t.length > 64) return 'Position must not exceed 64 characters.';
            return '';
        }

        function validateAddEmpDepartment(value) {
            if (value) return '';
            return addEmpShowRequired('department_id') ? 'Please select a department.' : '';
        }

        function validateAddEmpScheduleDeptSecretary() {
            var roleEl = addEmpField('portal_role');
            var deptEl = addEmpField('schedule_department_key');
            if (!roleEl || !deptEl) return '';
            if (roleEl.value !== 'secretary') return '';
            if (deptEl.value) return '';
            if (addEmpSubmitAttempted || addEmpTouched['schedule_department_key'] || addEmpTouched['portal_role']) {
                return 'Choose IT, EDUC, or SHTM for department management.';
            }
            return '';
        }

        function syncAddSecretaryManagedWrap() {
            var roleEl = addEmpField('portal_role');
            var wrap = document.getElementById('add_secretary_managed_wrap');
            if (!wrap || !roleEl) return;
            var show = roleEl.value === 'secretary';
            wrap.style.display = show ? '' : 'none';
            var dept = document.getElementById('add_emp_managed_dept');
            if (dept && !show) {
                dept.value = '';
            }
        }

        function validateAddEmpPassword(value) {
            const p = (value || '');
            if (p.length === 0) return '';
            if (p.length < PASS_MIN) return 'Password must be at least ' + PASS_MIN + ' characters.';
            return '';
        }

        function validateAddEmpEmail(emailVal, passwordVal) {
            const e = (emailVal || '').trim();
            const p = (passwordVal || '').trim();
            if (p.length > 0 && e.length === 0) {
                return 'Email is required when a login password is set.';
            }
            if (e.length > 0 && !EMAIL_RE.test(e)) {
                return 'Enter a valid email address.';
            }
            return '';
        }

        function validateAddEmpFace() {
            const fd = faceDescriptorInput && faceDescriptorInput.value;
            if (fd) return '';
            return addEmpSubmitAttempted ? 'Please capture your face before submitting.' : '';
        }

        function refreshAddEmployeeValidation() {
            syncEmployeeFullName();
            const surnameEl = addEmpField('surname');
            const firstEl = addEmpField('first_name');
            const middleEl = addEmpField('middle_name');
            const suffixEl = addEmpField('suffix');
            const nameEl = addEmpField('name');
            const posEl = addEmpField('position');
            const depEl = addEmpField('department_id');
            const emEl = addEmpField('email');
            const passEl = addEmpField('password');

            addEmpSetError('surname', surnameEl ? validateAddEmpSurname(surnameEl.value) : '');
            addEmpSetError('first_name', firstEl ? validateAddEmpFirstName(firstEl.value) : '');
            addEmpSetError('middle_name', middleEl ? validateAddEmpMiddleName(middleEl.value) : '');
            addEmpSetError('suffix', suffixEl ? validateAddEmpSuffix(suffixEl.value) : '');
            addEmpSetError('position', posEl ? validateAddEmpPosition(posEl.value) : '');
            addEmpSetError('department_id', depEl ? validateAddEmpDepartment(depEl.value) : '');
            addEmpSetError('password', passEl ? validateAddEmpPassword(passEl.value) : '');
            addEmpSetError('email', validateAddEmpEmail(emEl ? emEl.value : '', passEl ? passEl.value : ''));
            addEmpSetError('schedule_department_key', validateAddEmpScheduleDeptSecretary());
            addEmpSetError('face', validateAddEmpFace());
            syncAddSecretaryManagedWrap();
        }

        function addEmployeeFormHasErrors() {
            var errs = employeeForm.querySelectorAll('.add-emp-err');
            for (var i = 0; i < errs.length; i++) {
                if (errs[i].style.display === 'block' && errs[i].textContent) {
                    return true;
                }
            }
            return !!employeeForm.querySelector('.is-invalid');
        }

        window.refreshEmployeeAddValidation = refreshAddEmployeeValidation;
        window.markAddEmployeeSubmitAttempted = function () {
            addEmpSubmitAttempted = true;
        };
        window.addEmployeeFormHasErrors = addEmployeeFormHasErrors;

        window.resetAddEmployeeValidation = function () {
            addEmpSubmitAttempted = false;
            Object.keys(addEmpTouched).forEach(function (k) { delete addEmpTouched[k]; });
            employeeForm.querySelectorAll('.add-emp-err').forEach(function (el) {
                el.textContent = '';
                el.style.display = 'none';
            });
            employeeForm.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
        };

        ['surname', 'first_name', 'middle_name', 'suffix', 'position', 'email', 'password'].forEach(function (fieldName) {
            const el = addEmpField(fieldName);
            if (!el) return;
            el.addEventListener('blur', function () {
                addEmpTouched[fieldName] = true;
                refreshAddEmployeeValidation();
            });
            el.addEventListener('input', function () {
                refreshAddEmployeeValidation();
            });
        });

        ['department_id', 'schedule_department_key', 'portal_role'].forEach(function (fieldName) {
            const el = addEmpField(fieldName);
            if (!el) return;
            el.addEventListener('blur', function () {
                addEmpTouched[fieldName] = true;
                refreshAddEmployeeValidation();
            });
            el.addEventListener('change', function () {
                addEmpTouched[fieldName] = true;
                refreshAddEmployeeValidation();
            });
        });

        employeeForm.addEventListener('reset', function () {
            setTimeout(function () {
                window.resetAddEmployeeValidation();
            }, 0);
        });

        if (employeeForm) {
            syncAddSecretaryManagedWrap();
        }
    })();

    // Load face-api models
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
        faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
        faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/')
    ]).then(() => {
        modelsLoaded = true;
        console.log('Face API models loaded');
    }).catch(err => {
        console.error('Error loading Face API models:', err);
    });

    // Start camera
    if (startCameraBtn) {
        startCameraBtn.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;
                video.play();
                
                startCameraBtn.disabled = true;
                startCameraBtn.innerHTML = '<i class="fa fa-check"></i> Camera Started';
                captureFaceBtn.disabled = false;
                faceStatus.style.display = 'block';
                
                video.addEventListener('play', () => {
                    const displaySize = { width: 320, height: 240 };
                    faceapi.matchDimensions(canvas, displaySize);
                    
                    setInterval(async () => {
                        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks()
                            .withFaceDescriptors();
                        
                        const resizedDetections = faceapi.resizeResults(detections, displaySize);
                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                        faceapi.draw.drawDetections(canvas, resizedDetections);
                        faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
                        
                        if (detections.length > 0) {
                            capturedDescriptor = detections[0].descriptor;
                        }
                    }, 100);
                });
            } catch (err) {
                console.error('Error accessing camera:', err);
                alert('Could not access camera. Please make sure you have granted camera permissions.');
            }
        });
    }

    // Capture face
    if (captureFaceBtn) {
        captureFaceBtn.addEventListener('click', () => {
            if (!capturedDescriptor) {
                alert('No face detected! Please position your face in front of the camera.');
                return;
            }

            faceDescriptorInput.value = JSON.stringify(Array.from(capturedDescriptor));
            
            const captureCanvas = document.createElement('canvas');
            captureCanvas.width = video.videoWidth;
            captureCanvas.height = video.videoHeight;
            captureCanvas.getContext('2d').drawImage(video, 0, 0);
            faceImageInput.value = captureCanvas.toDataURL('image/jpeg');
            
            faceStatus.style.display = 'none';
            faceSuccess.style.display = 'block';
            
            captureFaceBtn.disabled = true;
            retakeFaceBtn.style.display = 'inline-block';
            
            video.pause();

            if (typeof window.refreshEmployeeAddValidation === 'function') {
                window.refreshEmployeeAddValidation();
            }
        });
    }

    // Retake face
    if (retakeFaceBtn) {
        retakeFaceBtn.addEventListener('click', () => {
            capturedDescriptor = null;
            faceDescriptorInput.value = '';
            faceImageInput.value = '';
            
            faceStatus.style.display = 'block';
            faceSuccess.style.display = 'none';
            
            captureFaceBtn.disabled = false;
            retakeFaceBtn.style.display = 'none';
            
            video.play();

            if (typeof window.refreshEmployeeAddValidation === 'function') {
                window.refreshEmployeeAddValidation();
            }
        });
    }

    // Form submission
    const employeeForm = document.getElementById('employeeForm');
    if (employeeForm) {
        employeeForm.addEventListener('submit', function(e) {
            if (typeof window.markAddEmployeeSubmitAttempted === 'function') {
                window.markAddEmployeeSubmitAttempted();
            }
            if (typeof window.refreshEmployeeAddValidation === 'function') {
                window.refreshEmployeeAddValidation();
            }

            if (typeof window.addEmployeeFormHasErrors === 'function' && window.addEmployeeFormHasErrors()) {
                e.preventDefault();
                let firstErr = employeeForm.querySelector('.is-invalid');
                if (!firstErr) {
                    employeeForm.querySelectorAll('.add-emp-err').forEach(function (node) {
                        if (!firstErr && node.style.display === 'block' && node.textContent) {
                            firstErr = node;
                        }
                    });
                }
                if (firstErr && firstErr.scrollIntoView) {
                    firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }

            return true;
        });
    }

    $('#addnew').on('shown.bs.modal', function () {
        if (typeof window.resetAddEmployeeValidation === 'function') {
            window.resetAddEmployeeValidation();
        }
        var dh = document.getElementById('add_emp_date_hired');
        if (dh && !dh.value) {
            dh.value = new Date().toISOString().slice(0, 10);
        }
        if (typeof window.refreshEmployeeAddValidation === 'function') {
            window.refreshEmployeeAddValidation();
        }
    });

    // Stop camera when modal is closed
    $('#addnew').on('hidden.bs.modal', function () {
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        if (startCameraBtn) {
            startCameraBtn.disabled = false;
            startCameraBtn.innerHTML = '<i class="fa fa-camera"></i> Start camera';
        }
        if (captureFaceBtn) captureFaceBtn.disabled = true;
        if (retakeFaceBtn) retakeFaceBtn.style.display = 'none';
        if (faceStatus) faceStatus.style.display = 'none';
        if (faceSuccess) faceSuccess.style.display = 'none';

        if (typeof window.resetAddEmployeeValidation === 'function') {
            window.resetAddEmployeeValidation();
        }
    });
</script>
<!-- Responsive-table-->

@endsection
