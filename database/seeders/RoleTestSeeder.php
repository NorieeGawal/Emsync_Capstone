<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class RoleTestSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::firstOrCreate(['name' => 'Production']);

        User::firstOrCreate(
            ['email' => 'hr@emsync.test'],
            [
                'name' => 'HR Test',
                'password' => Hash::make('password'),
                'role' => 'hr',
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@emsync.test'],
            [
                'name' => 'Manager Test',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'department_id' => $dept->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'employee@emsync.test'],
            [
                'name' => 'Employee Test',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department_id' => $dept->id,
            ]
        );
    }
}