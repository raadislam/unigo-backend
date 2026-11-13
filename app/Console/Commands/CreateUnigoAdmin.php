<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateUnigoAdmin extends Command
{
    protected $signature = 'unigo:admin-create';

    protected $description = 'Create a UniGo Super Admin account';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Administrator name'));
        $email = Str::lower(trim((string) $this->ask('Administrator email')));

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('A valid name and email address are required.');

            return self::FAILURE;
        }

        if (AdminUser::where('email', $email)->exists()) {
            $this->components->error('An administrator with this email already exists.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password');
        $confirmation = (string) $this->secret('Confirm password');

        if (strlen($password) < 12 || $password !== $confirmation) {
            $this->components->error('Passwords must match and contain at least 12 characters.');

            return self::FAILURE;
        }

        AdminUser::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->components->info('Super Admin account created successfully.');

        return self::SUCCESS;
    }
}
