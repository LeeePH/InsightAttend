<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private const FIXED_DEPARTMENTS = [
        'Student Services Department',
        'Admin Department',
        'Academic Department',
        'Finance Department',
        'Registrar Department',
        'HR department',
        'IT Department',
        'Education Department',
        'SHTM Department',
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->syncFixedDepartments();
        $departments = Department::query()->orderBy('name')->get();
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect()->route('departments.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        flash()->info('Info', 'Departments are fixed and managed automatically.');
        return redirect()->route('departments.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect()->route('departments.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect()->route('departments.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        flash()->info('Info', 'Departments are fixed and managed automatically.');
        return redirect()->route('departments.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        flash()->info('Info', 'Departments are fixed and managed automatically.');
        return redirect()->route('departments.index');
    }

    private function syncFixedDepartments(): void
    {
        self::ensureFixedDepartments();
    }

    public static function ensureFixedDepartments(): void
    {
        Department::query()
            ->whereNotIn('name', self::FIXED_DEPARTMENTS)
            ->delete();

        foreach (self::FIXED_DEPARTMENTS as $name) {
            Department::updateOrCreate(
                ['name' => $name],
                ['description' => null, 'is_active' => true]
            );
        }
    }
}
