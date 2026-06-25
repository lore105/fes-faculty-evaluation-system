<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class ImportController extends Controller
{
    public function importStudents(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $csv = Reader::createFromPath($request->file('file')->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $records = collect($csv->getRecords());
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($records as $index => $record) {
            $row = $index + 2;

            $validator = Validator::make($record, [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'student_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$row}: " . implode(', ', $validator->errors()->all());
                $skipped++;
                continue;
            }

            $existing = User::where('email', $record['email'])
                ->orWhere('student_id', $record['student_id'])
                ->first();

            if ($existing) {
                $errors[] = "Row {$row}: Student with email or ID already exists.";
                $skipped++;
                continue;
            }

            $user = User::create([
                'first_name' => $record['first_name'],
                'last_name' => $record['last_name'],
                'middle_name' => $record['middle_name'] ?? null,
                'name' => trim("{$record['first_name']} {$record['last_name']}"),
                'email' => $record['email'],
                'student_id' => $record['student_id'],
                'phone' => $record['phone'] ?? null,
                'gender' => $record['gender'] ?? null,
                'password' => Hash::make($record['student_id']),
                'is_active' => true,
            ]);

            $user->assignRole(RoleEnum::STUDENT->value);
            $imported++;
        }

        return response()->json([
            'success' => true,
            'message' => "Import completed. {$imported} students imported, {$skipped} skipped.",
            'data' => [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ],
        ]);
    }

    public function importFaculty(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $csv = Reader::createFromPath($request->file('file')->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $records = collect($csv->getRecords());
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($records as $index => $record) {
            $row = $index + 2;

            $validator = Validator::make($record, [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'employee_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$row}: " . implode(', ', $validator->errors()->all());
                $skipped++;
                continue;
            }

            $existing = User::where('email', $record['email'])
                ->orWhere('employee_id', $record['employee_id'])
                ->first();

            if ($existing) {
                $errors[] = "Row {$row}: Faculty with email or ID already exists.";
                $skipped++;
                continue;
            }

            $user = User::create([
                'first_name' => $record['first_name'],
                'last_name' => $record['last_name'],
                'middle_name' => $record['middle_name'] ?? null,
                'name' => trim("{$record['first_name']} {$record['last_name']}"),
                'email' => $record['email'],
                'employee_id' => $record['employee_id'],
                'phone' => $record['phone'] ?? null,
                'gender' => $record['gender'] ?? null,
                'password' => Hash::make($record['employee_id']),
                'is_active' => true,
            ]);

            $user->assignRole(RoleEnum::FACULTY->value);
            $imported++;
        }

        return response()->json([
            'success' => true,
            'message' => "Import completed. {$imported} faculty imported, {$skipped} skipped.",
            'data' => [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ],
        ]);
    }
}
