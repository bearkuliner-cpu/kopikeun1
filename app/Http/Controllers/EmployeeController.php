<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with([
            'user.role',
        ])
            ->latest()
            ->get();

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        $users = User::whereDoesntHave('employee')
            ->orderBy('name')
            ->get();

        $roles = Role::whereIn('nama_role', [
            'Karyawan',
            'Staff Operasional',
        ])
            ->orderBy('nama_role')
            ->get();

        return view('employees.create', compact(
            'users',
            'roles'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Account information
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],

            'role_id' => [
                'required',
                'exists:roles,id_role',
            ],

            // Employee information
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:Active,Inactive',
            ],
        ]);

        $role = Role::whereIn('nama_role', [
            'Karyawan',
            'Staff Operasional',
        ])
            ->where('id_role', $validated['role_id'])
            ->firstOrFail();

        DB::transaction(function () use ($validated, $role) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => $role->id_role,
            ]);

            // Create employee with temporary unique code.
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'TEMP-' . Str::uuid(),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'],
            ]);

            // Generate employee code based on employee ID.
            $sequence = $employee->id;

            do {
                $employeeCode = 'KK-EMP-' . str_pad(
                    $sequence,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                $sequence++;
            } while (
                Employee::where('employee_code', $employeeCode)->exists()
            );

            // Save generated employee code.
            $employee->update([
                'employee_code' => $employeeCode,
            ]);
        });

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        //
    }

   public function edit(Employee $employee): View
    {
        $users = User::whereHas('role', function ($query) {
            $query->whereIn('nama_role', [
                'Karyawan',
                'Staff Operasional',
            ]);
        })
        ->where(function ($query) use ($employee) {
            $query->whereDoesntHave('employee')
                ->orWhere('id', $employee->user_id);
        })
        ->orderBy('name')
        ->get();

        return view('employees.edit', compact(
            'employee',
            'users'
        ));
    }

    public function update(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                'unique:employees,user_id,' . $employee->id,
            ],

            'employee_code' => [
                'required',
                'string',
                'max:50',
                'unique:employees,employee_code,' . $employee->id,
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:Active,Inactive',
            ],
        ]);

        $employee->update([
            'user_id' => $validated['user_id'],
            'employee_code' => $validated['employee_code'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($employee) {
            /*
             * employees.user_id memiliki cascadeOnDelete(),
             * sehingga menghapus User akan menghapus Employee.
             */
            $employee->user->delete();
        });

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
