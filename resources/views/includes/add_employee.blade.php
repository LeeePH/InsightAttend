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
                                    <input type="text" class="form-control" placeholder="Job title" id="add_emp_position" name="position" required autocomplete="organization-title" maxlength="64" />
                                    <div class="add-emp-err text-danger small mt-1" data-for="position" role="alert" style="display:none;"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_department" class="font-weight-bold">Department</label>
                                    <select class="form-control" id="add_emp_department" name="department_id" required>
                                        <option value="" selected>Select department</option>
                                        @foreach(($departments ?? []) as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="add-emp-err text-danger small mt-1" data-for="department_id" role="alert" style="display:none;"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_portal_role" class="font-weight-bold">Role</label>
                                    <select class="form-control" id="add_emp_portal_role" name="portal_role" required>
                                        <option value="employee" {{ old('portal_role', 'employee') === 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="secretary" {{ old('portal_role') === 'secretary' ? 'selected' : '' }}>Secretary</option>
                                    </select>
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
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_email" class="font-weight-bold">Email</label>
                                    <input type="email" class="form-control" id="add_emp_email" name="email" autocomplete="email" placeholder="name@school.edu">
                                    <div class="add-emp-err text-danger small mt-1" data-for="email" role="alert" style="display:none;"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_phone" class="font-weight-bold">Phone (SMS)</label>
                                    <input type="text" class="form-control" id="add_emp_phone" name="phone" placeholder="+639xxxxxxxxx">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_date_hired" class="font-weight-bold">Date hired</label>
                                    <input type="date" class="form-control" id="add_emp_date_hired" name="date_hired" value="{{ old('date_hired', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_emp_employment_type" class="font-weight-bold">Employment type</label>
                                    <select class="form-control" id="add_emp_employment_type" name="employment_type">
                                        <option value="">Select</option>
                                        <option value="full_time">Full-time</option>
                                        <option value="part_time">Part-time</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="add_emp_password" class="font-weight-bold">Login password</label>
                                <input type="password" class="form-control" id="add_emp_password" name="password" placeholder="Optional — min. 8 characters" autocomplete="new-password" minlength="8">
                                <small class="text-muted d-block mt-1">If set, email is required. Role applies to that login.</small>
                                <div class="add-emp-err text-danger small mt-1" data-for="password" role="alert" style="display:none;"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="add_emp_skills" class="font-weight-bold">Skills &amp; expertise</label>
                                <textarea class="form-control" id="add_emp_skills" name="skills" rows="2" placeholder="One per line"></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label for="add_emp_achievements" class="font-weight-bold">Achievements</label>
                                <textarea class="form-control" id="add_emp_achievements" name="achievements" rows="2" placeholder="One per line"></textarea>
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
