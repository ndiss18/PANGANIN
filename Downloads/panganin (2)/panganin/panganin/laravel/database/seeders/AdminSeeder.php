<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('panganin.admin_email');
        $password = config('panganin.admin_password');

        if (! $email || ! $password) {
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'nama' => config('panganin.admin_name'),
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );
    }
}
