<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FingerDevicesControlller;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/profile', '\App\Http\Controllers\HomeController@profile')->name('profile')->middleware('auth');
Route::get('/lock-screen/view', '\App\Http\Controllers\HomeController@showLockScreen')->name('lock.screen.form');
Route::post('/lock-screen/unlock', '\App\Http\Controllers\HomeController@unlockScreen')->name('lock.screen.unlock');
Route::get('/lock-screen', '\App\Http\Controllers\HomeController@lockScreen')->name('lock.screen')->middleware('auth');

// Employee dashboard route
Route::get('/employee/dashboard', '\App\Http\Controllers\HomeController@employeeDashboard')->name('employee.dashboard')->middleware('auth');
Route::post('/employee/password', '\App\Http\Controllers\HomeController@updatePassword')->name('employee.password.update')->middleware('auth');
Route::get('/employee/attendance-logs', '\App\Http\Controllers\HomeController@employeeAttendanceLogs')->name('employee.attendance_logs')->middleware('auth');
Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['employee']], function () {
    Route::get('/employee/my-schedule', '\App\Http\Controllers\EmployeeTimetableController@mySchedule')->name('employee.my_schedule');
    Route::get('/employee/my-schedule/preview', '\App\Http\Controllers\EmployeeTimetableController@mySchedulePreview')->name('employee.my_schedule.preview');
    Route::get('/employee/my-schedule/pdf', '\App\Http\Controllers\EmployeeTimetableController@mySchedulePdf')->name('employee.my_schedule.pdf');
});
Route::get('/employee/settings', '\App\Http\Controllers\HomeController@employeeSettings')->name('employee.settings')->middleware('auth');
Route::post('/employee/settings/profile', '\App\Http\Controllers\HomeController@updateEmployeeProfile')->name('employee.settings.profile')->middleware('auth');
Route::post('/employee/settings/password', '\App\Http\Controllers\HomeController@updatePassword')->name('employee.settings.password')->middleware('auth');

// Time In routes (public)
Route::get('/timein', '\App\Http\Controllers\TimeInController@index')->name('timein.index');
Route::get('/timein/employees', '\App\Http\Controllers\TimeInController@getEmployees')->name('timein.employees');
Route::get('/timein/employee/{name}', '\App\Http\Controllers\TimeInController@getEmployee')->name('timein.employee');
Route::post('/timein', '\App\Http\Controllers\TimeInController@store')->name('timein.store');

// Time Out routes (public)
Route::get('/timeout', '\App\Http\Controllers\TimeInController@timeoutIndex')->name('timeout.index');
Route::post('/timeout', '\App\Http\Controllers\TimeInController@storeTimeout')->name('timeout.store');

// Leave Request routes (authenticated users)
Route::get('/leave/request', '\App\Http\Controllers\LeaveController@requestForm')->name('leave.request')->middleware('auth');
Route::post('/leave/request', '\App\Http\Controllers\LeaveController@storeRequest')->name('leave.storeRequest')->middleware('auth');

// Resignation Request routes (authenticated users)
Route::get('/resignation/request', '\App\Http\Controllers\ResignationController@requestForm')->name('resignation.request')->middleware('auth');
Route::post('/resignation/request', '\App\Http\Controllers\ResignationController@storeRequest')->name('resignation.storeRequest')->middleware('auth');

// Loan Application routes (authenticated users)
Route::get('/loan/request', '\App\Http\Controllers\LoanController@requestForm')->name('loan.request')->middleware('auth');
Route::post('/loan/request', '\App\Http\Controllers\LoanController@storeRequest')->name('loan.storeRequest')->middleware('auth');
Route::get('/loan/{id}/view', '\App\Http\Controllers\LoanController@show')->name('loan.show')->middleware('auth');
Route::get('/loan/{id}/attachments/{index}', '\App\Http\Controllers\LoanController@downloadAttachment')->name('loan.attachment')->middleware('auth');

// Discount Application routes (authenticated users)
Route::get('/discount/request', '\App\Http\Controllers\DiscountApplicationController@requestForm')->name('discount.request')->middleware('auth');
Route::post('/discount/request', '\App\Http\Controllers\DiscountApplicationController@storeRequest')->name('discount.storeRequest')->middleware('auth');
Route::get('/discount/{id}/view', '\App\Http\Controllers\DiscountApplicationController@show')->name('discount.show')->middleware('auth');
Route::get('/discount/{id}/attachments/{index}', '\App\Http\Controllers\DiscountApplicationController@downloadAttachment')->name('discount.attachment')->middleware('auth');

// Overtime Authorization routes (authenticated users)
Route::get('/overtime-authorization/request', '\App\Http\Controllers\OvertimeAuthorizationController@requestForm')->name('overtime_authorization.request')->middleware('auth');
Route::post('/overtime-authorization/request', '\App\Http\Controllers\OvertimeAuthorizationController@storeRequest')->name('overtime_authorization.storeRequest')->middleware('auth');
Route::get('/overtime-authorization/{id}/view', '\App\Http\Controllers\OvertimeAuthorizationController@show')->name('overtime_authorization.show')->middleware('auth');
Route::get('/undertime-authorization/request', '\App\Http\Controllers\UndertimeAuthorizationController@requestForm')->name('undertime_authorization.request')->middleware('auth');
Route::post('/undertime-authorization/request', '\App\Http\Controllers\UndertimeAuthorizationController@storeRequest')->name('undertime_authorization.storeRequest')->middleware('auth');
Route::get('/undertime-authorization/{id}/view', '\App\Http\Controllers\UndertimeAuthorizationController@show')->name('undertime_authorization.show')->middleware('auth');

// Permit to Teach (Outside School) routes (authenticated users)
Route::get('/permit-to-teach-outside/request', '\App\Http\Controllers\PermitToTeachOutsideController@requestForm')->name('permit_to_teach_outside.request')->middleware('auth');
Route::post('/permit-to-teach-outside/request', '\App\Http\Controllers\PermitToTeachOutsideController@storeRequest')->name('permit_to_teach_outside.storeRequest')->middleware('auth');
Route::get('/permit-to-teach-outside/{id}/view', '\App\Http\Controllers\PermitToTeachOutsideController@show')->name('permit_to_teach_outside.show')->middleware('auth');
Route::get('/subsitution/request', '\App\Http\Controllers\SubstitutionController@requestForm')->name('substitution.request')->middleware('auth');
Route::post('/subsitution/request', '\App\Http\Controllers\SubstitutionController@storeRequest')->name('substitution.storeRequest')->middleware('auth');
Route::get('/subsitution/{id}/view', '\App\Http\Controllers\SubstitutionController@show')->name('substitution.show')->middleware('auth');

// Feedback routes removed (feature disabled)

// Leave approval letter route
Route::get('/leave/approval-letter/{id}', '\App\Http\Controllers\LeaveController@generateApprovalLetter')->name('leave.approvalLetter');

Route::get('attended/{user_id}', '\App\Http\Controllers\AttendanceController@attended' )->name('attended');
Route::get('attended-before/{user_id}', '\App\Http\Controllers\AttendanceController@attendedBefore' )->name('attendedBefore');
Auth::routes(['register' => false, 'reset' => false]);

Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin']], function () {
    Route::get('/employees/{id}/profile', '\App\Http\Controllers\EmployeeController@profile')->name('employees.profile');
    Route::resource('/employees', '\App\Http\Controllers\EmployeeController');
    Route::resource('/employees', '\App\Http\Controllers\EmployeeController');
    Route::get('/attendance', '\App\Http\Controllers\AttendanceController@index')->name('attendance');
   
    Route::get('/latetime', '\App\Http\Controllers\AttendanceController@indexLatetime')->name('indexLatetime');
    Route::get('/leave', '\App\Http\Controllers\LeaveController@index')->name('leave');
    Route::post('/leave', '\App\Http\Controllers\LeaveController@store')->name('leave.store.admin');
    Route::put('/leave/{id}/approve', '\App\Http\Controllers\LeaveController@approve')->name('leave.approve');
    Route::put('/leave/{id}/reject', '\App\Http\Controllers\LeaveController@reject')->name('leave.reject');
    Route::delete('/leave/{id}', '\App\Http\Controllers\LeaveController@destroy')->name('leave.destroy');
    Route::get('/resignation', '\App\Http\Controllers\ResignationController@adminIndex')->name('resignation.admin');
    Route::put('/resignation/{id}/approve', '\App\Http\Controllers\ResignationController@approve')->name('resignation.approve');
    Route::put('/resignation/{id}/reject', '\App\Http\Controllers\ResignationController@reject')->name('resignation.reject');
    Route::get('/resignation/approval-letter/{id}', '\App\Http\Controllers\ResignationController@generateApprovalLetter')->name('resignation.approvalLetter');
    Route::get('/loan', '\App\Http\Controllers\LoanController@adminIndex')->name('loan.admin');
    Route::put('/loan/{id}/approve', '\App\Http\Controllers\LoanController@approve')->name('loan.approve');
    Route::put('/loan/{id}/reject', '\App\Http\Controllers\LoanController@reject')->name('loan.reject');
    Route::delete('/loan/{id}', '\App\Http\Controllers\LoanController@destroy')->name('loan.destroy');
    Route::get('/loan/approval-letter/{id}', '\App\Http\Controllers\LoanController@generateApprovalLetterPdf')->name('loan.approvalLetterPdf');
    Route::get('/discount', '\App\Http\Controllers\DiscountApplicationController@adminIndex')->name('discount.admin');
    Route::put('/discount/{id}/approve', '\App\Http\Controllers\DiscountApplicationController@approve')->name('discount.approve');
    Route::put('/discount/{id}/reject', '\App\Http\Controllers\DiscountApplicationController@reject')->name('discount.reject');
    Route::delete('/discount/{id}', '\App\Http\Controllers\DiscountApplicationController@destroy')->name('discount.destroy');
    Route::get('/discount/approval-letter/{id}', '\App\Http\Controllers\DiscountApplicationController@generateApprovalLetterPdf')->name('discount.approvalLetterPdf');
    Route::get('/overtime-authorization', '\App\Http\Controllers\OvertimeAuthorizationController@adminIndex')->name('overtime_authorization.admin');
    Route::put('/overtime-authorization/{id}/approve', '\App\Http\Controllers\OvertimeAuthorizationController@approve')->name('overtime_authorization.approve');
    Route::put('/overtime-authorization/{id}/reject', '\App\Http\Controllers\OvertimeAuthorizationController@reject')->name('overtime_authorization.reject');
    Route::delete('/overtime-authorization/{id}', '\App\Http\Controllers\OvertimeAuthorizationController@destroy')->name('overtime_authorization.destroy');
    Route::get('/overtime-authorization/approval-letter/{id}', '\App\Http\Controllers\OvertimeAuthorizationController@generateApprovalLetterPdf')->name('overtime_authorization.approvalLetterPdf');
    Route::get('/undertime-authorization', '\App\Http\Controllers\UndertimeAuthorizationController@adminIndex')->name('undertime_authorization.admin');
    Route::put('/undertime-authorization/{id}/approve', '\App\Http\Controllers\UndertimeAuthorizationController@approve')->name('undertime_authorization.approve');
    Route::put('/undertime-authorization/{id}/reject', '\App\Http\Controllers\UndertimeAuthorizationController@reject')->name('undertime_authorization.reject');
    Route::delete('/undertime-authorization/{id}', '\App\Http\Controllers\UndertimeAuthorizationController@destroy')->name('undertime_authorization.destroy');
    Route::get('/undertime-authorization/approval-letter/{id}', '\App\Http\Controllers\UndertimeAuthorizationController@generateApprovalLetterPdf')->name('undertime_authorization.approvalLetterPdf');
    Route::get('/permit-to-teach-outside', '\App\Http\Controllers\PermitToTeachOutsideController@adminIndex')->name('permit_to_teach_outside.admin');
    Route::put('/permit-to-teach-outside/{id}/approve', '\App\Http\Controllers\PermitToTeachOutsideController@approve')->name('permit_to_teach_outside.approve');
    Route::put('/permit-to-teach-outside/{id}/reject', '\App\Http\Controllers\PermitToTeachOutsideController@reject')->name('permit_to_teach_outside.reject');
    Route::delete('/permit-to-teach-outside/{id}', '\App\Http\Controllers\PermitToTeachOutsideController@destroy')->name('permit_to_teach_outside.destroy');
    Route::get('/permit-to-teach-outside/approval-letter/{id}', '\App\Http\Controllers\PermitToTeachOutsideController@generateApprovalLetterPdf')->name('permit_to_teach_outside.approvalLetterPdf');
    Route::get('/subsitution', '\App\Http\Controllers\SubstitutionController@adminIndex')->name('substitution.admin');
    Route::put('/subsitution/{id}/approve', '\App\Http\Controllers\SubstitutionController@approve')->name('substitution.approve');
    Route::put('/subsitution/{id}/reject', '\App\Http\Controllers\SubstitutionController@reject')->name('substitution.reject');
    Route::delete('/subsitution/{id}', '\App\Http\Controllers\SubstitutionController@destroy')->name('substitution.destroy');
    Route::get('/subsitution/approval-letter/{id}', '\App\Http\Controllers\SubstitutionController@generateApprovalLetterPdf')->name('substitution.approvalLetterPdf');
    Route::get('/overtime', '\App\Http\Controllers\LeaveController@indexOvertime')->name('indexOvertime');

    // Feedback routes removed (feature disabled)
    Route::get('/admin/backups', '\App\Http\Controllers\BackupController@index')->name('admin.backups');
    Route::post('/admin/backups/create', '\App\Http\Controllers\BackupController@create')->name('admin.backups.create');
    Route::get('/admin/backups/{backup}/download', '\App\Http\Controllers\BackupController@download')->name('admin.backups.download');
    Route::post('/admin/backups/{backup}/restore', '\App\Http\Controllers\BackupController@restore')->name('admin.backups.restore');
    Route::post('/admin/backups/upload-restore', '\App\Http\Controllers\BackupController@uploadAndRestore')->name('admin.backups.upload_restore');
    Route::post('/admin/backups/reset-database', '\App\Http\Controllers\BackupController@resetDatabase')->name('admin.backups.reset_database');
    Route::post('/admin/backups/delete-database', '\App\Http\Controllers\BackupController@deleteDatabase')->name('admin.backups.delete_database');
    Route::get('/admin/user-management', '\App\Http\Controllers\UserManagementController@index')->name('admin.user_management');
    Route::put('/admin/user-management/{user}', '\App\Http\Controllers\UserManagementController@update')->name('admin.user_management.update');
    Route::get('/admin/audit-logs', '\App\Http\Controllers\AuditLogController@index')->name('admin.audit_logs');
    Route::get('/admin/maintenance', '\App\Http\Controllers\MaintenanceController@index')->name('admin.maintenance');
    Route::post('/admin/maintenance', '\App\Http\Controllers\MaintenanceController@store')->name('admin.maintenance.store');

    Route::get('/admin', '\App\Http\Controllers\AdminController@index')->name('admin');

    Route::resource('/departments', '\App\Http\Controllers\DepartmentController');
    Route::get('/department-reports', '\App\Http\Controllers\DepartmentReportController@index')->name('departments.report');

    Route::get('/check', '\App\Http\Controllers\CheckController@index')->name('check');
    Route::get('/sheet-report', '\App\Http\Controllers\CheckController@sheetReport')->name('sheet-report');
    Route::post('check-store','\App\Http\Controllers\CheckController@CheckStore')->name('check_store');
    
    // Fingerprint Devices
    Route::resource('/finger_device', '\App\Http\Controllers\BiometricDeviceController');

    Route::delete('finger_device/destroy', '\App\Http\Controllers\BiometricDeviceController@massDestroy')->name('finger_device.massDestroy');
    Route::get('finger_device/{fingerDevice}/employees/add', '\App\Http\Controllers\BiometricDeviceController@addEmployee')->name('finger_device.add.employee');
    Route::get('finger_device/{fingerDevice}/get/attendance', '\App\Http\Controllers\BiometricDeviceController@getAttendance')->name('finger_device.get.attendance');
    // Temp Clear Attendance route
    Route::get('finger_device/clear/attendance', function () {
        $midnight = \Carbon\Carbon::createFromTime(23, 50, 00);
        $diff = now()->diffInMinutes($midnight);
        dispatch(new ClearAttendanceJob())->delay(now()->addMinutes($diff));
        toast("Attendance Clearance Queue will run in 11:50 P.M}!", "success");

        return back();
    })->name('finger_device.clear.attendance');
    

});

Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin', 'hr', 'secretary']], function () {
    Route::get('/employee-timetable/pdf/section/{classSection}', '\App\Http\Controllers\EmployeeTimetableController@sectionPdf')->name('employee_timetable.section_pdf');
    Route::get('/employee-timetable/pdf', '\App\Http\Controllers\EmployeeTimetableController@pdf')->name('employee_timetable.pdf');
    Route::get('/class-sections', '\App\Http\Controllers\ClassSectionController@index')->name('class_sections.index');
    Route::post('/class-sections', '\App\Http\Controllers\ClassSectionController@store')->name('class_sections.store');
    Route::put('/class-sections/{classSection}', '\App\Http\Controllers\ClassSectionController@update')->name('class_sections.update');
    Route::delete('/class-sections/{classSection}', '\App\Http\Controllers\ClassSectionController@destroy')->name('class_sections.destroy');
    Route::get('/employee-timetable', '\App\Http\Controllers\EmployeeTimetableController@index')->name('employee_timetable.index');
    Route::post('/employee-timetable', '\App\Http\Controllers\EmployeeTimetableController@store')->name('employee_timetable.store');
    Route::put('/employee-timetable/{entry}', '\App\Http\Controllers\EmployeeTimetableController@update')->name('employee_timetable.update');
    Route::delete('/employee-timetable/{entry}', '\App\Http\Controllers\EmployeeTimetableController@destroy')->name('employee_timetable.destroy');
});

Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin', 'hr']], function () {
    Route::get('/admin/maintenance-form', '\App\Http\Controllers\MaintenanceController@formBuilder')->name('admin.maintenance_form');
    Route::post('/admin/maintenance-form/courses', '\App\Http\Controllers\MaintenanceController@storeCourse')->name('admin.maintenance_form.courses.store');
    Route::put('/admin/maintenance-form/courses/{course}', '\App\Http\Controllers\MaintenanceController@updateCourse')->name('admin.maintenance_form.courses.update');
    Route::delete('/admin/maintenance-form/courses/{course}', '\App\Http\Controllers\MaintenanceController@destroyCourse')->name('admin.maintenance_form.courses.destroy');
    Route::post('/admin/maintenance-form/timetable-settings', '\App\Http\Controllers\MaintenanceController@updateTimetableSettings')->name('admin.maintenance_form.timetable_settings.update');
});

Route::group(['middleware' => ['auth']], function () {

    // Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/notifications', '\App\Http\Controllers\NotificationController@index')->name('notifications.index');
    Route::post('/notifications/{id}/read', '\App\Http\Controllers\NotificationController@markRead')->name('notifications.read');
    Route::post('/notifications/read-all', '\App\Http\Controllers\NotificationController@markAllRead')->name('notifications.read_all');



    

});

// Route::get('/attendance/assign', function () {
//     return view('attendance_leave_login');
// })->name('attendance.login');

// Route::post('/attendance/assign', '\App\Http\Controllers\AttendanceController@assign')->name('attendance.assign');


// Route::get('/leave/assign', function () {
//     return view('attendance_leave_login');
// })->name('leave.login');

// Route::post('/leave/assign', '\App\Http\Controllers\LeaveController@assign')->name('leave.assign');


// Route::get('{any}', 'App\http\controllers\VeltrixController@index');
