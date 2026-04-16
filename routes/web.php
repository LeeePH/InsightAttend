<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FingerDevicesControlller;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Employee dashboard route
Route::get('/employee/dashboard', '\App\Http\Controllers\HomeController@employeeDashboard')->name('employee.dashboard')->middleware('auth');
Route::post('/employee/password', '\App\Http\Controllers\HomeController@updatePassword')->name('employee.password.update')->middleware('auth');

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

// Feedback routes (employee)
Route::get('/employee/feedback', '\App\Http\Controllers\FeedbackController@index')->name('employee.feedback')->middleware('auth');
Route::post('/employee/feedback', '\App\Http\Controllers\FeedbackController@store')->name('employee.feedback.store')->middleware('auth');

// Feedback routes (admin)

// Leave approval letter route
Route::get('/leave/approval-letter/{id}', '\App\Http\Controllers\LeaveController@generateApprovalLetter')->name('leave.approvalLetter');

Route::get('attended/{user_id}', '\App\Http\Controllers\AttendanceController@attended' )->name('attended');
Route::get('attended-before/{user_id}', '\App\Http\Controllers\AttendanceController@attendedBefore' )->name('attendedBefore');
Auth::routes(['register' => false, 'reset' => false]);

Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin']], function () {
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
    Route::get('/overtime', '\App\Http\Controllers\LeaveController@indexOvertime')->name('indexOvertime');

    // Feedback (admin)
    Route::get('/admin/feedback', '\App\Http\Controllers\FeedbackController@adminIndex')->name('admin.feedback');
    Route::get('/admin/feedback/{id}/read', '\App\Http\Controllers\FeedbackController@markRead')->name('admin.feedback.read');
    Route::get('/admin/feedback/{id}/resolve', '\App\Http\Controllers\FeedbackController@markResolved')->name('admin.feedback.resolve');
    Route::get('/admin/backups', '\App\Http\Controllers\BackupController@index')->name('admin.backups');
    Route::post('/admin/backups/create', '\App\Http\Controllers\BackupController@create')->name('admin.backups.create');
    Route::get('/admin/backups/{backup}/download', '\App\Http\Controllers\BackupController@download')->name('admin.backups.download');
    Route::post('/admin/backups/{backup}/restore', '\App\Http\Controllers\BackupController@restore')->name('admin.backups.restore');
    Route::post('/admin/backups/upload-restore', '\App\Http\Controllers\BackupController@uploadAndRestore')->name('admin.backups.upload_restore');
    Route::get('/admin/user-management', '\App\Http\Controllers\UserManagementController@index')->name('admin.user_management');
    Route::put('/admin/user-management/{user}', '\App\Http\Controllers\UserManagementController@update')->name('admin.user_management.update');
    Route::get('/admin/audit-logs', '\App\Http\Controllers\AuditLogController@index')->name('admin.audit_logs');
    Route::get('/admin/maintenance-form', '\App\Http\Controllers\MaintenanceController@formBuilder')->name('admin.maintenance_form');
    Route::post('/admin/maintenance-form/templates', '\App\Http\Controllers\MaintenanceController@storeTemplate')->name('admin.maintenance_form.templates.store');
    Route::put('/admin/maintenance-form/templates/{template}', '\App\Http\Controllers\MaintenanceController@updateTemplate')->name('admin.maintenance_form.templates.update');
    Route::delete('/admin/maintenance-form/templates/{template}', '\App\Http\Controllers\MaintenanceController@destroyTemplate')->name('admin.maintenance_form.templates.destroy');
    Route::post('/admin/maintenance-form/templates/{template}/fields', '\App\Http\Controllers\MaintenanceController@storeTemplateField')->name('admin.maintenance_form.fields.store');
    Route::put('/admin/maintenance-form/templates/{template}/fields/{field}', '\App\Http\Controllers\MaintenanceController@updateTemplateField')->name('admin.maintenance_form.fields.update');
    Route::delete('/admin/maintenance-form/templates/{template}/fields/{field}', '\App\Http\Controllers\MaintenanceController@destroyTemplateField')->name('admin.maintenance_form.fields.destroy');
    Route::get('/admin/maintenance', '\App\Http\Controllers\MaintenanceController@index')->name('admin.maintenance');
    Route::post('/admin/maintenance', '\App\Http\Controllers\MaintenanceController@store')->name('admin.maintenance.store');

    Route::get('/admin', '\App\Http\Controllers\AdminController@index')->name('admin');

    Route::resource('/schedule', '\App\Http\Controllers\ScheduleController');

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

Route::group(['middleware' => ['auth']], function () {

    // Route::get('/home', 'HomeController@index')->name('home');



    

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
