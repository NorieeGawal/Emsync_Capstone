<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class HrDepartmentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = $validated['search'] ?? null;

        $departments = Department::withCount('users')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        return view('hr.departments.index', compact('departments', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create($validated);

        return redirect()->route('hr.departments.index')->with('success', 'Department added successfully.');
    }

    public function destroy(Department $department)
    {
        // Preserve the department while employee records still reference it.
        if ($department->users()->count() > 0) {
            return redirect()->route('hr.departments.index')->with('error', 'Cannot delete a department with assigned employees.');
        }

        $department->delete();
        return redirect()->route('hr.departments.index')->with('success', 'Department removed.');
    }
}