<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('ADMIN_EMAIL must contain a valid email address.');
        }

        if (! is_string($password) || $password === '') {
            throw new InvalidArgumentException('ADMIN_PASSWORD must be configured.');
        }

        $admin = User::query()->firstOrNew(['email' => $email]);

        if (! $admin->exists) {
            $admin->forceFill([
                'name' => config('admin.name'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
        }

        $admin->forceFill(['is_admin' => true])->save();
    }
}
