<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class HrEmployeeController extends Controller
{
        public function index(Request $request){
        $search = $request->query('search');

        $employees = User::with('department')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('role')
            ->paginate(10)
            ->withQueryString();

        return view('hr.employees.index', compact('employees', 'search'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('hr.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:hr,manager,employee',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
        ]);

        return redirect()->route('hr.employees.index')->with('success', ucfirst($validated['role']) . ' account created successfully.');
    }

    public function edit(User $employee)
    {
        $departments = Department::all();
        return view('hr.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'role' => 'required|in:hr,manager,employee',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $employee->update($validated);

        return redirect()->route('hr.employees.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(User $employee)
    {
        $employee->delete();
        return redirect()->route('hr.employees.index')->with('success', 'Account removed.');
    }
}