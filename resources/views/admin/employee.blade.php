@extends('layouts.master')

@section('css')
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
<a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="mdi mdi-plus mr-2"></i>Add</a>
        

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
                                                <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        
                                                    <thead>
                                                    <tr>
                                                        <th data-priority="1">Employee ID</th>
                                                        <th data-priority="2">Name</th>
                                                        <th data-priority="3">Position</th>
                                                        <th data-priority="4">Department</th>
                                                        <th data-priority="5">Email</th>
                                                        <th data-priority="5">Schedule</th>
                                                        <th data-priority="6">Member Since</th>
                                                        <th data-priority="7">Actions</th>
                                                     
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach( $employees as $employee)

                                                        <tr>
                                                            <td>{{$employee->id}}</td>
                                                            <td>{{$employee->name}}</td>
                                                            <td>{{$employee->position}}</td>
                                                            <td>{{$employee->department ?? 'N/A'}}</td>
                                                            <td>{{$employee->email}}</td>
                                                            <td>
                                                                @if(isset($employee->schedules->first()->slug))
                                                                {{$employee->schedules->first()->slug}}
                                                                @endif
                                                            </td>
                                                            <td>{{$employee->created_at}}</td>
                                                            <td>
                        
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
                            </div> <!-- end col -->
                        </div> <!-- end row -->    
                                    

@foreach( $employees as $employee)
@include('includes.edit_delete_employee')
@endforeach

@include('includes.add_employee')

@endsection


@section('script')
<!-- Face API JS -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
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

        const NAME_RE = /^[A-Za-z][A-Za-z\s.'-]*$/;
        const POSITION_RE = /^[A-Za-z0-9][A-Za-z0-9\s.\-/&]*$/;
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

        function validateAddEmpName(value) {
            const t = (value || '').trim();
            if (t.length === 0) {
                return addEmpShowRequired('name') ? 'Name is required.' : '';
            }
            if (t.length < 3) return 'Name must be at least 3 characters.';
            if (t.length > 64) return 'Name must not exceed 64 characters.';
            if (!NAME_RE.test(t)) {
                return 'Name may contain letters, spaces, apostrophes, dots, and hyphens only (must start with a letter).';
            }
            return '';
        }

        function validateAddEmpPosition(value) {
            const t = (value || '').trim();
            if (t.length === 0) {
                return addEmpShowRequired('position') ? 'Position is required.' : '';
            }
            if (t.length < 2) return 'Position must be at least 2 characters.';
            if (t.length > 64) return 'Position must not exceed 64 characters.';
            if (!POSITION_RE.test(t)) {
                return 'Position may contain letters, numbers, spaces, dots, hyphens, slashes, and ampersands only (must start with a letter or number).';
            }
            return '';
        }

        function validateAddEmpDepartment(value) {
            if (value) return '';
            return addEmpShowRequired('department') ? 'Please select a department.' : '';
        }

        function validateAddEmpSchedule(value) {
            if (value) return '';
            return addEmpShowRequired('schedule') ? 'Please select a schedule.' : '';
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
            const nameEl = addEmpField('name');
            const posEl = addEmpField('position');
            const depEl = addEmpField('department');
            const emEl = addEmpField('email');
            const passEl = addEmpField('password');
            const schEl = addEmpField('schedule');

            addEmpSetError('name', nameEl ? validateAddEmpName(nameEl.value) : '');
            addEmpSetError('position', posEl ? validateAddEmpPosition(posEl.value) : '');
            addEmpSetError('department', depEl ? validateAddEmpDepartment(depEl.value) : '');
            addEmpSetError('password', passEl ? validateAddEmpPassword(passEl.value) : '');
            addEmpSetError('email', validateAddEmpEmail(emEl ? emEl.value : '', passEl ? passEl.value : ''));
            addEmpSetError('schedule', schEl ? validateAddEmpSchedule(schEl.value) : '');
            addEmpSetError('face', validateAddEmpFace());
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

        ['name', 'position', 'email', 'password'].forEach(function (fieldName) {
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

        ['department', 'schedule'].forEach(function (fieldName) {
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
    });

    // Stop camera when modal is closed
    $('#addnew').on('hidden.bs.modal', function () {
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        if (startCameraBtn) {
            startCameraBtn.disabled = false;
            startCameraBtn.innerHTML = '<i class="fa fa-camera"></i> Start Camera';
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