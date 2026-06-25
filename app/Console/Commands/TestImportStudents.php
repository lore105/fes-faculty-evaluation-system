<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;

class TestImportStudents extends Command
{
    protected $signature = 'fes:test-import';
    protected $description = 'Test student import';

    public function handle()
    {
        $students = [
            ['first_name' => 'Pedro', 'last_name' => 'Reyes', 'email' => 'pedro.reyes@fes.com', 'student_id' => '2025-0002'],
            ['first_name' => 'Ana', 'last_name' => 'Gonzales', 'email' => 'ana.gonzales@fes.com', 'student_id' => '2025-0003'],
            ['first_name' => 'Carlo', 'last_name' => 'Mendoza', 'email' => 'carlo.mendoza@fes.com', 'student_id' => '2025-0004'],
        ];

        foreach ($students as $student) {
            $user = User::firstOrCreate(
                ['email' => $student['email']],
                [
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'name' => "{$student['first_name']} {$student['last_name']}",
                    'email' => $student['email'],
                    'student_id' => $student['student_id'],
                    'password' => Hash::make($student['student_id']),
                    'is_active' => true,
                ]
            );

            $user->assignRole(RoleEnum::STUDENT->value);
            $this->info("Imported: {$user->full_name}");
        }

        $this->info('Import test completed!');
    }
}
