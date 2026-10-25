<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LogicException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('admin_seeder.email');
        $administrator = User::query()->firstOrNew(['email' => $email]);

        $administrator->fill([
            'name' => (string) config('admin_seeder.name'),
            'email_verified_at' => now(),
            'role' => 'super_admin',
        ]);

        if (! $administrator->exists) {
            $administrator->password = Hash::make($this->password());
        }

        $administrator->save();
    }

    private function password(): string
    {
        $configuredPassword = config('admin_seeder.password');

        if (filled($configuredPassword)) {
            return (string) $configuredPassword;
        }

        if (app()->isProduction()) {
            throw new LogicException('ADMIN_SEEDER_PASSWORD mesti ditetapkan sebelum menjalankan AdminUserSeeder dalam produksi.');
        }

        return 'password';
    }
}
