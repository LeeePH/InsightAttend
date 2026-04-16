<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>

            </div>

            <h4 class="modal-title"><b>Add Employee</b></h4>
            <div class="modal-body">

                <div class="card-body text-left">

                    <form method="POST" action="{{ route('employees.store') }}" id="employeeForm" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_emp_name">Name</label>
                                    <input type="text" class="form-control" placeholder="Enter Employee Name" id="add_emp_name" name="name"
                                        required autocomplete="name" maxlength="64" />
                                    <small class="form-text text-muted">At least 3 letters; letters, spaces, apostrophes, dots, and hyphens only.</small>
                                    <div class="add-emp-err text-danger small mt-1" data-for="name" role="alert" style="display:none;"></div>
                                </div>
                                <div class="form-group">
                                    <label for="add_emp_position">Position</label>
                                    <input type="text" class="form-control" placeholder="Enter Employee Position" id="add_emp_position" name="position"
                                        required autocomplete="organization-title" maxlength="64" />
                                    <small class="form-text text-muted">At least 2 characters; letters, numbers, spaces, dots, hyphens, slashes, and ampersands.</small>
                                    <div class="add-emp-err text-danger small mt-1" data-for="position" role="alert" style="display:none;"></div>
                                </div>
                                <div class="form-group">
                                    <label for="add_emp_department">Department</label>
                                    <select class="form-control" id="add_emp_department" name="department" required>
                                        <option value="" selected>- Select Department -</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Finance">Finance</option>
                                        <option value="HR">HR</option>
                                        <option value="Registrar">Registrar</option>
                                        <option value="SIT">SIT</option>
                                        <option value="SED">SED</option>
                                        <option value="OEVP">OEVP</option>
                                        <option value="Student Service">Student Service</option>
                                        <option value="Academic">Academic</option>
                                        <option value="SHTM">SHTM</option>
                                    </select>
                                    <div class="add-emp-err text-danger small mt-1" data-for="department" role="alert" style="display:none;"></div>
                                </div>

                                
                                <div class="form-group">
                                    <label for="add_emp_email" class="col-sm-3 control-label">Email</label>


                                    <input type="email" class="form-control" id="add_emp_email" name="email" autocomplete="email">

                                    <div class="add-emp-err text-danger small mt-1" data-for="email" role="alert" style="display:none;"></div>
                                </div>
                                <div class="form-group">
                                    <label for="add_emp_password" class="col-sm-3 control-label">Password</label>
                                    <input type="password" class="form-control" id="add_emp_password" name="password" placeholder="Login Password" autocomplete="new-password" minlength="8">
                                    <small class="text-muted d-block mt-1">Optional. If set, must be at least 8 characters and email is required.</small>
                                    <div class="add-emp-err text-danger small mt-1" data-for="password" role="alert" style="display:none;"></div>
                                </div>
                                <div class="form-group">
                                    <label class="text-info"><strong>Login Credentials (Optional)</strong></label>
                                    <p class="text-muted">If provided, employee can login to their dashboard</p>
                                </div>
                                <div class="form-group">
                                    <label for="add_emp_schedule" class="col-sm-3 control-label">Schedule</label>


                                    <select class="form-control" id="add_emp_schedule" name="schedule" required>
                                        <option value="" selected>- Select -</option>
                                        @foreach($schedules as $schedule)
                                        <option value="{{$schedule->slug}}">{{$schedule->slug}} -> from {{$schedule->time_in}}
                                            to {{$schedule->time_out}} </option>
                                        @endforeach

                                    </select>
                                    <div class="add-emp-err text-danger small mt-1" data-for="schedule" role="alert" style="display:none;"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Face Registration</strong></label>
                                    <div id="faceCaptureContainer" style="position: relative; width: 320px; height: 240px; margin: 0 auto;">
                                        <video id="video" width="320" height="240" style="position: absolute; border: 2px solid #4a90e2; border-radius: 5px;"></video>
                                        <canvas id="canvas" width="320" height="240" style="position: absolute; border: 2px solid #4a90e2; border-radius: 5px;"></canvas>
                                    </div>
                                    <div style="text-align: center; margin-top: 10px;">
                                        <button type="button" id="startCamera" class="btn btn-info btn-sm">
                                            <i class="fa fa-camera"></i> Start Camera
                                        </button>
                                        <button type="button" id="captureFace" class="btn btn-primary btn-sm" disabled>
                                            <i class="fa fa-camera-retro"></i> Capture Face
                                        </button>
                                        <button type="button" id="retakeFace" class="btn btn-warning btn-sm" style="display: none;">
                                            <i class="fa fa-refresh"></i> Retake
                                        </button>
                                    </div>
                                    <input type="hidden" id="face_descriptor" name="face_descriptor">
                                    <input type="hidden" id="face_image" name="face_image">
                                    <div id="faceStatus" class="alert alert-info mt-2" style="display: none;">
                                        <small>Face not captured yet. Please capture your face.</small>
                                    </div>
                                    <div id="faceSuccess" class="alert alert-success mt-2" style="display: none;">
                                        <small><i class="fa fa-check"></i> Face captured successfully!</small>
                                    </div>
                                    <div class="add-emp-err text-danger small mt-2" data-for="face" role="alert" style="display:none;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light" id="submitBtn">
                                    Submit
                                </button>
                                <button type="reset" class="btn btn-secondary waves-effect m-l-5" data-dismiss="modal">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>


        </div>

    </div>
</div>
</div>
