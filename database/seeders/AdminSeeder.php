<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@fes.com'],
            [
                'first_name' => 'FES',
                'last_name' => 'Administrator',
                'name' => 'FES Administrator',
                'email' => 'admin@fes.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole(RoleEnum::ADMINISTRATOR->value);

        $this->command->info('FES Admin user seeded successfully!');
        $this->command->info('Email: admin@fes.com');
        $this->command->info('Password: password');
    }
}
